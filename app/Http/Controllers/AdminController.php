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
        $songs = Song::latest()->paginate(10);
        return view('admin.pages.music', compact('songs'));
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
