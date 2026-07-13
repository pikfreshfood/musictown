<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Create your Music Town account and start streaming music.">
    <title>Signup - Music Town</title>
    @include('partials.favicon')
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700,800" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <main class="auth-page">
        <a class="brand auth-brand" href="{{ url('/') }}" aria-label="Music Town home">
            @include('partials.brand-mark')
            <span>Music Town</span>
        </a>

        <section class="auth-shell">
            <div class="auth-copy">
                <p class="eyebrow">Start listening</p>
                <h1>Create your music identity.</h1>
                <p>Join Music Town to stream songs, earn rewards while listening, and withdraw your earnings.</p>

                <div class="auth-benefits">
                    <span>Stream & earn</span>
                    <span>Instant withdrawal</span>
                    <span>Referral bonus</span>
                </div>
            </div>

            <form class="auth-card" method="POST" action="{{ route('signup.submit') }}">
                @csrf
                @if ($ref = request()->query('ref'))
                    <input type="hidden" name="ref" value="{{ $ref }}">
                @endif
                <div class="auth-card-heading">
                    <p class="eyebrow">Signup</p>
                    <h2>Open your account</h2>
                </div>

                @if (session('status'))
                    <p class="form-message success-message">{{ session('status') }}</p>
                @endif

                @if ($errors->any())
                    <div class="form-message error-message">
                        @foreach ($errors->all() as $error)
                            <p>{{ $error }}</p>
                        @endforeach
                    </div>
                @endif

                <label>
                    Full name
                    <input type="text" name="name" value="{{ old('name') }}" placeholder="Your full name" autocomplete="name" required>
                </label>

                <label>
                    Email address
                    <input type="email" name="email" value="{{ old('email') }}" placeholder="you@example.com" autocomplete="email" required>
                </label>

                <label>
                    Password
                    <span class="password-field">
                        <input type="password" name="password" placeholder="Create password" autocomplete="new-password" required>
                        <button class="password-toggle" type="button" aria-label="Show password" data-password-toggle>
                            <svg class="icon-eye" viewBox="0 0 24 24" aria-hidden="true">
                                <path d="M2.5 12s3.5-6 9.5-6 9.5 6 9.5 6-3.5 6-9.5 6-9.5-6-9.5-6Z"></path>
                                <circle cx="12" cy="12" r="3"></circle>
                            </svg>
                            <svg class="icon-eye-off" viewBox="0 0 24 24" aria-hidden="true">
                                <path d="m3 3 18 18"></path>
                                <path d="M10.6 5.2A10.7 10.7 0 0 1 12 5c6 0 9.5 7 9.5 7a16.6 16.6 0 0 1-2.2 3.1"></path>
                                <path d="M6.1 6.7C3.7 8.4 2.5 12 2.5 12s3.5 7 9.5 7a9.8 9.8 0 0 0 4.1-.9"></path>
                                <path d="M9.9 9.9a3 3 0 0 0 4.2 4.2"></path>
                            </svg>
                        </button>
                    </span>
                </label>

                <label class="check-field terms-check">
                    <input type="checkbox" name="terms" required>
                    <span>I agree to the Music Town terms and conditions.</span>
                </label>

                <button class="button auth-submit" type="submit">Create Account</button>

                <p class="auth-switch">
                    Already have an account?
                    <a href="{{ route('login') }}">Login</a>
                </p>
            </form>
        </section>
    </main>
</body>
</html>
