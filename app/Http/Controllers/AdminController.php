<?php

namespace App\Http\Controllers;

use App\Models\NcoinCode;
use App\Models\NcoinPayment;
use App\Models\PaymentAccount;
use App\Models\PremiumPayment;
use App\Models\Song;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Services\AudiusService;
use App\Services\JamendoService;
use Illuminate\Support\Facades\Storage;

class AdminController extends Controller
{
    private function guard()
    {
        if (!Auth::user() || !Auth::user()->is_admin) {
            abort(redirect('/'));
        }
    }

    private function isSuperAdmin()
    {
        return Auth::user()->role === 'super_admin';
    }

    // ─── AUTH ───

    public function loginForm()
    {
        return view('admin.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $username = strtolower($credentials['username']);
        $user = User::where('is_admin', true)
            ->where(function ($query) use ($username) {
                $query->whereRaw('LOWER(name) = ?', [$username])
                    ->orWhereRaw('LOWER(email) = ?', [$username]);
            })
            ->first();

        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            return back()->withErrors(['username' => 'Invalid admin credentials.']);
        }

        Auth::login($user);

        return redirect()->route('admin.dashboard');
    }

    // ─── DASHBOARD ───

    public function dashboard()
    {
        $this->guard();
        $stats = [
            'users' => User::where('is_admin', false)->count(),
            'songs' => Song::count(),
            'pending_payments' => PremiumPayment::where('status', 'pending')->count(),
            'pending_ncoin' => NcoinPayment::where('status', 'pending')->count(),
            'total_balance' => User::where('is_admin', false)->sum('balance'),
        ];
        $recentUsers = User::where('is_admin', false)->latest()->take(5)->get();
        return view('admin.pages.dashboard', compact('stats', 'recentUsers'));
    }

    // ─── USERS ───

    public function users()
    {
        $this->guard();
        $q = request('q');
        $tier = request('tier');
        $is_premium = request('is_premium');

        $usersQuery = User::where('is_admin', false)
            ->with(['referrer', 'referrals', 'paystackVirtualAccount'])
            ->when($q, function ($builder, $term) {
                $term = trim($term);
                $builder->where(function ($b) use ($term) {
                    $b->where('name', 'like', "%{$term}%")
                      ->orWhere('email', 'like', "%{$term}%")
                      ->orWhere('phone', 'like', "%{$term}%")
                      ->orWhere('referral_code', 'like', "%{$term}%");
                });
            })
            ->when($tier, function ($builder, $t) {
                $builder->where('tier', $t);
            })
            ->when(isset($is_premium) && $is_premium !== '', function ($builder) use ($is_premium) {
                $builder->where('is_premium', $is_premium ? 1 : 0);
            });

        $users = $usersQuery->orderBy('created_at', 'desc')
            ->paginate(20)
            ->appends(request()->only('q', 'tier', 'is_premium'));

        $tiers = User::whereNotNull('tier')->distinct()->pluck('tier');

        return view('admin.pages.users', compact('users', 'q', 'tier', 'is_premium', 'tiers'));
    }

    public function showUser($id)
    {
        $this->guard();
        $user = User::with(['referrer', 'referrals.paystackVirtualAccount', 'paystackVirtualAccount'])->findOrFail($id);
        return view('admin.pages.user-detail', compact('user'));
    }

    public function userReferrals($id)
    {
        $this->guard();
        $user = User::with(['referrals.paystackVirtualAccount'])->findOrFail($id);
        return view('admin.pages.user-referrals', compact('user'));
    }

    public function notifications()
    {
        $this->guard();
        $recipients = User::where('is_admin', false)
            ->whereNotNull('email')
            ->pluck('email')
            ->filter()
            ->unique()
            ->values()
            ->all();

        return view('admin.pages.notifications', compact('recipients'));
    }

    public function sendNotifications(Request $request)
    {
        $this->guard();

        $validated = $request->validate([
            'subject' => ['required', 'string', 'max:191'],
            'message' => ['required', 'string'],
            'recipients_csv' => ['required', 'file', 'mimes:csv,txt', 'max:2048'],
        ]);

        $recipients = $this->extractEmailsFromCsv($request->file('recipients_csv'));

        if (empty($recipients)) {
            return back()->with('error', 'Uploaded CSV contains no valid email addresses. Please upload a file with one or more valid email addresses.')->withInput();
        }

        $fromAddress = config('mail.from.address', 'no-reply@example.com');
        $fromName = config('mail.from.name', config('app.name', 'PulseWave'));

        $primaryRecipient = array_shift($recipients);
        $bccRecipients = $recipients;

        try {
            Mail::send('admin.pages.notifications-email', ['messageContent' => $validated['message'], 'subject' => $validated['subject']], function ($message) use ($primaryRecipient, $bccRecipients, $validated, $fromAddress, $fromName) {
                $message->from($fromAddress, $fromName);
                $message->to($primaryRecipient);

                if (Auth::user() && filter_var(Auth::user()->email, FILTER_VALIDATE_EMAIL)) {
                    $message->cc(Auth::user()->email);
                }

                if (!empty($bccRecipients)) {
                    $message->bcc($bccRecipients);
                }
                $message->subject($validated['subject']);
            });
        } catch (\Exception $exception) {
            Log::error('Failed to send notification email', [
                'exception' => $exception,
                'recipients' => $recipients,
                'subject' => $validated['subject'],
            ]);

            return back()->with('error', 'Failed to send email: ' . $exception->getMessage())->withInput();
        }

        return redirect()->route('admin.notifications')->with('success', 'Promotional email sent to ' . (1 + count($bccRecipients)) . ' recipient(s).');
    }

    private function extractEmailsFromCsv($file): array
    {
        $emails = [];
        if (!$file->isValid()) {
            return $emails;
        }

        $path = $file->getRealPath();
        if (!$path || !is_readable($path)) {
            return $emails;
        }

        $content = file_get_contents($path);
        if ($content === false) {
            return $emails;
        }

        // Normalize BOM and encodings.
        $content = preg_replace('/^\xEF\xBB\xBF/', '', $content);
        if (function_exists('mb_detect_encoding')) {
            $encoding = mb_detect_encoding($content, ['UTF-8', 'UTF-16LE', 'UTF-16BE', 'ISO-8859-1'], true);
            if ($encoding && $encoding !== 'UTF-8') {
                $content = mb_convert_encoding($content, 'UTF-8', $encoding);
            }
        }

        $emails = [];

        // First pass: extract all valid email-like values from the raw text.
        preg_match_all('/[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,}/', $content, $matches);
        if (!empty($matches[0])) {
            $emails = $matches[0];
        }

        // Second pass: if no emails were found, tokenize by common delimiters and validate.
        if (empty($emails)) {
            $normalized = str_replace(["\r\n", "\r", "\t", ',', ';'], "\n", $content);
            $lines = explode("\n", $normalized);
            foreach ($lines as $line) {
                $line = trim($line);
                if ($line === '') {
                    continue;
                }
                $tokens = preg_split('/[\s,;]+/', $line);
                foreach ($tokens as $token) {
                    $token = trim($token, " \t\n\r\0\x0B\"'<>:\/\\");
                    if (stripos($token, 'mailto:') === 0) {
                        $token = substr($token, 7);
                    }
                    if (filter_var($token, FILTER_VALIDATE_EMAIL)) {
                        $emails[] = $token;
                    }
                }
            }
        }

        return collect($emails)
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    public function deleteUser($id)
    {
        $this->guard();
        $user = User::findOrFail($id);
        if ($user->is_admin) {
            return redirect()->route('admin.users')->with('error', 'Cannot delete admin users here.');
        }
        // Unlink referrals (do not delete referred users)
        User::where('referrer_id', $user->id)->update(['referrer_id' => null]);

        // Delete related listens, withdrawals, premium & ncoin payments, wallet fundings and transactions
        $user->listens()->delete();
        $user->withdrawals()->delete();
        $user->premiumPayments()->delete();
        NcoinPayment::where('user_id', $user->id)->delete();

        // Delete paystack virtual account and its related fundings/transactions if present
        if ($user->paystackVirtualAccount) {
            $user->paystackVirtualAccount->fundings()->delete();
            $user->paystackVirtualAccount->transactions()->delete();
            $user->paystackVirtualAccount->delete();
        }

        // Delete wallet fundings/transactions directly related to user
        $user->walletFundings()->delete();
        $user->walletTransactions()->delete();

        // Finally delete the user
        $user->delete();

        return redirect()->route('admin.users')->with('success', 'User and related account information deleted successfully.');
    }

    // ─── MUSIC ───

    public function music()
    {
        $this->guard();
        $q = request('q');
        $songs = Song::when($q, function ($query, $search) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('artist', 'like', "%{$search}%");
            });
        })->latest()->paginate(10)->withQueryString();
        return view('admin.pages.music', compact('songs', 'q'));
    }

    public function loadMoreMusic(Request $request)
    {
        $this->guard();
        $q = $request->input('q');
        $page = (int) $request->input('page', 2);

        $songs = Song::when($q, function ($query, $search) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('artist', 'like', "%{$search}%");
            });
        })->latest()->paginate(10, ['*'], 'page', $page);

        $html = '';
        foreach ($songs as $song) {
            $mins = intdiv($song->duration, 60);
            $secs = $song->duration % 60;
            $html .= '<div class="admin-song-card">';
            $html .= '<span class="admin-song-info"><strong>' . e($song->title) . '</strong> <small>' . e($song->artist) . '</small></span>';
            $html .= '<small>' . $mins . ':' . str_pad($secs, 2, '0') . '</small>';
            $html .= '<span style="color:var(--green);font-size:0.8rem;font-weight:700;">' . ($song->audio_url ? 'Yes' : 'No') . '</span>';
            $html .= '<a class="btn btn-danger btn-sm" href="' . route('admin.music.delete', $song->id) . '" onclick="return confirm(\'Delete ' . e($song->title) . '?\')">Delete</a>';
            $html .= '</div>';
        }

        return response()->json([
            'html' => $html,
            'hasMore' => $songs->hasMorePages(),
            'nextPage' => $page + 1,
        ]);
    }

    public function uploadMusic(Request $request)
    {
        $this->guard();
        $request->validate([
            'music_files' => ['required', 'array'],
            'music_files.*' => ['file', 'max:128000'],
        ]);

        $allowed = ['mp3', 'wav', 'ogg', 'aac', 'm4a', 'flac', 'wma'];
        $uploaded = 0;
        foreach ($request->file('music_files') as $file) {
            $ext = strtolower($file->getClientOriginalExtension());
            if (!in_array($ext, $allowed)) {
                continue;
            }
            $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            $title = $this->extractTitleFromFilename($originalName);
            $hash = md5(uniqid(mt_rand(), true));
            $filename = $hash . '.' . $ext;
            $path = $file->storeAs('music', $filename, 'public');
            Song::create([
                'title' => $title,
                'artist' => 'Uploaded',
                'duration' => 180,
                'audio_url' => $path,
            ]);
            $uploaded++;
        }

        return redirect()->route('admin.music')->with('success', "$uploaded song(s) uploaded successfully.");
    }

    public function deleteMusic($id)
    {
        $this->guard();
        $song = Song::findOrFail($id);
        if ($song->audio_url) {
            Storage::disk('public')->delete($song->audio_url);
        }
        $song->delete();
        return redirect()->route('admin.music')->with('success', 'Song deleted successfully.');
    }

    public function syncAudius(AudiusService $audius)
    {
        $this->guard();

        $keywords = ['nigeria', 'naija', 'afrobeat', 'afropop', 'lagos', 'africa'];
        $total = 0;

        foreach ($keywords as $keyword) {
            try {
                $tracks = $audius->searchTracks($keyword, 50);
            } catch (\RuntimeException $e) {
                continue;
            }

            foreach ($tracks as $track) {
                try {
                    Song::updateOrCreate(
                        ['audius_id' => $track['audius_id']],
                        [
                            'title' => $track['title'],
                            'artist' => $track['artist'],
                            'duration' => max($track['duration'], 30),
                            'audio_url' => $track['audio_url'],
                            'image_url' => $track['image_url'],
                        ]
                    );
                    $total++;
                } catch (\Throwable) {
                }
            }
        }

        return redirect()->route('admin.music')->with('success', "Imported {$total} track(s) from Audius.");
    }

    public function deleteAllMusic()
    {
        $this->guard();
        $songs = Song::all();
        foreach ($songs as $song) {
            if ($song->audio_url) {
                Storage::disk('public')->delete($song->audio_url);
            }
            $song->delete();
        }
        return redirect()->route('admin.music')->with('success', 'All music deleted successfully.');
    }

    public function syncJamendo(Request $request, JamendoService $jamendo)
    {
        $this->guard();

        $request->validate([
            'limit' => ['integer', 'min:1', 'max:200'],
            'tag' => ['nullable', 'string'],
        ]);

        $limit = $request->integer('limit', 50);
        $tag = $request->input('tag');

        $params = ['limit' => $limit];
        if ($tag && $tag !== 'all') {
            $params['tags'] = $tag;
        }

        try {
            $tracks = $jamendo->getTracks($params);
        } catch (\RuntimeException $e) {
            return redirect()->route('admin.music')->with('error', $e->getMessage());
        }

        if (empty($tracks)) {
            return redirect()->route('admin.music')->with('error', 'No tracks found from Jamendo API.');
        }

        $imported = 0;
        $skipped = 0;

        foreach ($tracks as $track) {
            $existing = Song::where('jamendo_id', $track['jamendo_id'])->first();
            if ($existing) {
                $skipped++;
                continue;
            }

            Song::create([
                'jamendo_id' => $track['jamendo_id'],
                'title' => $track['title'],
                'artist' => $track['artist'],
                'duration' => max($track['duration'], 30),
                'audio_url' => $track['audio_url'],
                'image_url' => $track['image_url'],
            ]);

            $imported++;
        }

        return redirect()->route('admin.music')->with(
            'success',
            "Imported {$imported} track(s) from Jamendo. Skipped {$skipped} existing."
        );
    }

    private function extractTitleFromFilename($filename)
    {
        $title = str_replace(['_', '-', '.'], ' ', $filename);
        $title = preg_replace('/\s+/', ' ', $title);
        $title = trim($title);
        return ucwords(strtolower($title));
    }

    // ─── SUB ADMINS ───

    public function subAdmins()
    {
        $this->guard();
        $admins = User::where('is_admin', true)->where('id', '!=', Auth::id())->get();
        return view('admin.pages.sub-admins', compact('admins'));
    }

    public function createSubAdmin(Request $request)
    {
        $this->guard();
        if (!$this->isSuperAdmin()) {
            return redirect()->route('admin.sub-admins')->with('error', 'Only super admins can create sub-admins.');
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'unique:users,email'],
            'phone' => ['required', 'string', 'max:30'],
            'password' => ['required', 'string', 'min:6'],
        ]);

        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'password' => Hash::make($validated['password']),
            'balance' => 0,
            'is_admin' => true,
            'is_premium' => true,
            'role' => 'admin',
        ]);

        return redirect()->route('admin.sub-admins')->with('success', 'Sub-admin created successfully.');
    }

    public function deleteSubAdmin($id)
    {
        $this->guard();
        if (!$this->isSuperAdmin()) {
            return redirect()->route('admin.sub-admins')->with('error', 'Only super admins can delete sub-admins.');
        }

        $admin = User::findOrFail($id);
        if (!$admin->is_admin) {
            return redirect()->route('admin.sub-admins')->with('error', 'User is not an admin.');
        }
        if ($admin->id === Auth::id()) {
            return redirect()->route('admin.sub-admins')->with('error', 'You cannot delete yourself.');
        }

        $admin->delete();
        return redirect()->route('admin.sub-admins')->with('success', 'Sub-admin deleted successfully.');
    }

    // ─── PREMIUM PAYMENTS ───

    public function payments()
    {
        $this->guard();
        $payments = PremiumPayment::with('user')->latest()->paginate(20);
        return view('admin.pages.payments', compact('payments'));
    }

    public function approvePremium($paymentId)
    {
        $this->guard();
        $payment = PremiumPayment::findOrFail($paymentId);
        $payment->update(['status' => 'approved']);
        $payment->user->update(['is_premium' => true]);
        return redirect()->route('admin.payments')->with('success', 'Premium upgrade approved.');
    }

    public function rejectPremium($paymentId)
    {
        $this->guard();
        $payment = PremiumPayment::findOrFail($paymentId);
        $payment->update(['status' => 'rejected']);
        return redirect()->route('admin.payments')->with('success', 'Premium payment rejected.');
    }

    // ─── NCOIN PAYMENTS ───

    public function ncoinPayments()
    {
        $this->guard();
        $payments = NcoinPayment::with('user')->latest()->paginate(20);
        return view('admin.pages.ncoin-payments', compact('payments'));
    }

    public function approveNcoin($paymentId)
    {
        $this->guard();
        $payment = NcoinPayment::findOrFail($paymentId);
        $payment->update(['status' => 'approved']);
        $payment->user->update(['is_premium' => true]);
        return redirect()->route('admin.ncoin-payments')->with('success', 'Ncoin payment approved. User upgraded to premium.');
    }

    public function rejectNcoin($paymentId)
    {
        $this->guard();
        $payment = NcoinPayment::findOrFail($paymentId);
        $payment->update(['status' => 'rejected']);
        return redirect()->route('admin.ncoin-payments')->with('success', 'Ncoin payment rejected.');
    }

    // ─── NCOIN CODES ───

    public function ncoinCodes()
    {
        $this->guard();
        $codes = NcoinCode::latest()->paginate(30);
        return view('admin.pages.ncoin-codes', compact('codes'));
    }

    public function generateNcoinCode(Request $request)
    {
        $this->guard();
        $request->validate(['count' => ['integer', 'min:1', 'max:20']]);
        $count = $request->integer('count', 1);

        $generated = [];
        for ($i = 0; $i < $count; $i++) {
            $code = strtoupper(substr(bin2hex(random_bytes(4)), 0, 8));
            NcoinCode::create([
                'code' => $code,
                'generated_by' => Auth::id(),
            ]);
            $generated[] = $code;
        }

        $msg = $count === 1
            ? "Ncoin code <strong>{$generated[0]}</strong> generated successfully."
            : count($generated) . ' Ncoin codes generated successfully.';

        return redirect()->route('admin.ncoin-codes')->with('success', $msg);
    }

    public function deleteNcoinCode($id)
    {
        $this->guard();
        $code = NcoinCode::findOrFail($id);
        if ($code->is_used) {
            return redirect()->route('admin.ncoin-codes')->with('error', 'Cannot delete an already-used code.');
        }
        $code->delete();
        return redirect()->route('admin.ncoin-codes')->with('success', 'Ncoin code deleted.');
    }

    // ─── PAYMENT ACCOUNT ───

    public function paymentAccount()
    {
        $this->guard();
        $account = PaymentAccount::where('is_active', true)->first();
        return view('admin.pages.payment-account', compact('account'));
    }

    public function savePaymentAccount(Request $request)
    {
        $this->guard();

        $account = PaymentAccount::where('is_active', true)->first();

        $rules = [
            'bank_name' => ['required', 'string', 'max:255'],
            'account_number' => ['required', 'string', 'max:20'],
            'account_name' => ['required', 'string', 'max:255'],
            'ncoin_amount' => ['nullable', 'numeric', 'min:0'],
            'telegram_username' => ['nullable', 'string', 'max:255'],
        ];

        if (!$account) {
            $rules['pin'] = ['required', 'string', 'size:6'];
            $rules['pin_confirmation'] = ['required', 'string', 'same:pin'];
            $validated = $request->validate($rules);

            PaymentAccount::create([
                'bank_name' => $validated['bank_name'],
                'account_number' => $validated['account_number'],
                'account_name' => $validated['account_name'],
                'ncoin_amount' => $validated['ncoin_amount'] ?? null,
                'telegram_username' => $validated['telegram_username'] ?? null,
                'pin' => Hash::make($validated['pin']),
                'is_active' => true,
            ]);
        } else {
            $rules['pin'] = ['required', 'string'];
            $validated = $request->validate($rules);

            if (!Hash::check($validated['pin'], $account->pin)) {
                return back()->withErrors(['pin' => 'The PIN you entered is incorrect.']);
            }

            PaymentAccount::where('is_active', true)->update(['is_active' => false]);
            PaymentAccount::create([
                'bank_name' => $validated['bank_name'],
                'account_number' => $validated['account_number'],
                'account_name' => $validated['account_name'],
                'ncoin_amount' => $validated['ncoin_amount'] ?? $account->ncoin_amount,
                'telegram_username' => $validated['telegram_username'] ?? $account->telegram_username,
                'pin' => $account->pin,
                'is_active' => true,
            ]);
        }

        return redirect()->route('admin.payment-account')->with('success', 'Payment account saved successfully.');
    }

    // ─── SETTINGS ───

    public function settings()
    {
        $this->guard();
        return view('admin.pages.settings');
    }

    public function updatePassword(Request $request)
    {
        $this->guard();
        $validated = $request->validate([
            'current_password' => ['required', 'string'],
            'new_password' => ['required', 'string', 'min:6', 'confirmed'],
        ]);

        $user = Auth::user();

        if (!Hash::check($validated['current_password'], $user->password)) {
            return back()->withErrors(['current_password' => 'Current password is incorrect.']);
        }

        $user->update(['password' => Hash::make($validated['new_password'])]);

        return redirect()->route('admin.settings')->with('success', 'Password updated successfully.');
    }
}
