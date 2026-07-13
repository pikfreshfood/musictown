@extends('layouts.user')

@section('title', 'Settings')
@section('page-title', 'Settings')
@section('meta-description', 'Update your Music Town profile settings.')

@section('content')
        <section style="max-width:600px;margin:0 auto;">
            <div class="section-heading">
                <p class="eyebrow">Profile</p>
                <h2 style="font-size:1.1rem;">Settings</h2>
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

                <button class="button auth-submit" type="submit">Save Changes</button>
            </form>
        </section>

    <style>
        .form-message {
            padding: 12px 16px;
            border-radius: 8px;
            font-weight: 700;
            font-size: 0.9rem;
            margin-bottom: 16px;
        }
        .success-message {
            background: rgba(59, 130, 246, 0.12);
            border: 1px solid rgba(59, 130, 246, 0.4);
            color: #60a5fa;
        }
        .error-message {
            background: rgba(220, 38, 38, 0.12);
            border: 1px solid rgba(220, 38, 38, 0.4);
            color: #f87171;
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
            border: 1px solid rgba(59, 130, 246, 0.25);
            border-radius: 8px;
            color: white;
            min-height: 52px;
            outline: 0;
            padding: 0 16px;
            transition: border-color 180ms ease, box-shadow 180ms ease;
        }
        .auth-card input:focus {
            border-color: rgba(59, 130, 246, 0.74);
            box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.16);
        }
        .auth-submit {
            border: 0;
            cursor: pointer;
            width: 100%;
            margin-top: 4px;
        }
    </style>
@endsection
