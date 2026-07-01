<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Update your PulseWave profile settings.">
    <title>Settings - PulseWave</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700,800" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <header class="site-header">
        <a class="brand" href="{{ route('profile') }}" aria-label="PulseWave home">
            <span class="brand-mark">P</span>
            <span>PulseWave</span>
        </a>
        <button class="menu-toggle" type="button" aria-label="Open navigation" aria-expanded="false" data-menu-toggle>
            <span></span><span></span><span></span>
        </button>
        <nav class="site-nav" data-site-nav>
            <a href="{{ route('profile') }}">Dashboard</a>
            <a href="{{ route('profile.settings') }}">Settings</a>
            <a href="{{ route('profile.withdrawal') }}">Withdrawal</a>
            <a href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">Logout</a>
        </nav>
        <div class="auth-links">
            <span style="color:var(--gold);font-weight:700;">{{ $user->name }}</span>
        </div>
    </header>

    <form id="logout-form" method="POST" action="{{ route('logout') }}" style="display:none;">@csrf</form>

    <main style="padding: 120px clamp(20px, 5vw, 72px) 40px;">
        <section style="max-width:600px;margin:0 auto;">
            <div class="section-heading">
                <p class="eyebrow">Profile</p>
                <h2>Settings</h2>
            </div>

            @if (session('success'))
                <p class="form-message success-message">{{ session('success') }}</p>
            @endif

            @if ($errors->any())
                <div class="form-message error-message">
                    @foreach ($errors->all() as $error)
                        <p>{{ $error }}</p>
                    @endforeach
                </div>
            @endif

            <form class="auth-card" method="POST" action="{{ route('profile.update') }}">
                @csrf

                <label>
                    Full name
                    <input type="text" name="name" value="{{ old('name', $user->name) }}" required>
                </label>

                <label>
                    Email address
                    <input type="email" name="email" value="{{ old('email', $user->email) }}" required>
                </label>

                <label>
                    Phone number
                    <input type="tel" name="phone" value="{{ old('phone', $user->phone) }}" required>
                </label>

                <button class="button auth-submit" type="submit">Save Changes</button>
            </form>
        </section>
    </main>

    <style>
        .form-message {
            padding: 12px 16px;
            border-radius: 8px;
            font-weight: 700;
            font-size: 0.9rem;
            margin-bottom: 16px;
        }
        .success-message {
            background: rgba(72, 181, 255, 0.12);
            border: 1px solid rgba(72, 181, 255, 0.4);
            color: #48b5ff;
        }
        .error-message {
            background: rgba(255, 50, 50, 0.12);
            border: 1px solid rgba(255, 50, 50, 0.4);
            color: #ff6b6b;
        }
        .auth-card label {
            color: #dce7f8;
            display: grid;
            font-size: 0.9rem;
            font-weight: 800;
            gap: 9px;
        }
        .auth-card input {
            background: rgba(2, 6, 14, 0.82);
            border: 1px solid rgba(72, 181, 255, 0.25);
            border-radius: 8px;
            color: white;
            min-height: 52px;
            outline: 0;
            padding: 0 16px;
            transition: border-color 180ms ease, box-shadow 180ms ease;
        }
        .auth-card input:focus {
            border-color: rgba(255, 122, 26, 0.74);
            box-shadow: 0 0 0 4px rgba(20, 118, 255, 0.16);
        }
        .auth-submit {
            border: 0;
            cursor: pointer;
            width: 100%;
            margin-top: 4px;
        }
    </style>
</body>
</html>
