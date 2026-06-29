
<div class="login-card">
    <style>
        /* ── Form card ────────────────────────────────────────── */
        .login-card { width: 100%; }
    
        .login-header { margin-bottom: 28px; }
        .login-header h5 {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--ink);
            line-height: 1.2;
        }
        .login-header p {
            font-size: .875rem;
            color: var(--ink-muted);
            margin-top: 6px;
        }
    
        /* alert */
        .login-alert {
            background: #FEE2E2;
            border: 1px solid #FECACA;
            border-radius: 10px;
            color: #991B1B;
            padding: 10px 14px;
            font-size: .85rem;
            margin-bottom: 20px;
        }
    
        /* field */
        .field { margin-bottom: 18px; }
        .field label {
            display: block;
            font-size: .8rem;
            font-weight: 600;
            color: var(--ink);
            margin-bottom: 6px;
            letter-spacing: .01em;
        }
        .field-wrap { position: relative; }
        .field-icon {
            position: absolute;
            left: 13px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--ink-muted);
            font-size: 1rem;
            pointer-events: none;
        }
        .field input {
            width: 100%;
            padding: 11px 14px 11px 40px;
            border: 1.5px solid var(--border);
            border-radius: 10px;
            font-size: .925rem;
            color: var(--ink);
            background: var(--surface);
            outline: none;
            transition: border-color .15s, box-shadow .15s;
        }
        .field input:focus {
            border-color: var(--brand-red);
            box-shadow: 0 0 0 3px rgba(232,32,12,.1);
        }
        .field input.is-invalid { border-color: #EF4444; }
        .invalid-msg {
            font-size: .78rem;
            color: #DC2626;
            margin-top: 5px;
        }
    
        /* submit */
        .btn-login {
            width: 100%;
            padding: 13px;
            background: var(--brand-red);
            color: #fff;
            font-size: .95rem;
            font-weight: 700;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            transition: background .15s, transform .1s;
            margin-top: 8px;
        }
        .btn-login:hover:not(:disabled)  { background: var(--brand-red-dk); }
        .btn-login:active:not(:disabled) { transform: scale(.99); }
        .btn-login:disabled { opacity: .65; cursor: not-allowed; }
    
        /* divider */
        .login-divider {
            display: flex;
            align-items: center;
            gap: 12px;
            margin: 22px 0;
            color: var(--ink-muted);
            font-size: .8rem;
        }
        .login-divider::before,
        .login-divider::after {
            content: '';
            flex: 1;
            height: 1px;
            background: var(--border);
        }
    
        /* register link */
        .login-register {
            text-align: center;
            font-size: .875rem;
            color: var(--ink-muted);
        }
        .login-register a {
            color: var(--brand-red);
            font-weight: 600;
            text-decoration: none;
        }
        .login-register a:hover { text-decoration: underline; }
    
        /* mobile only: show mini brand */
        .mobile-brand {
            display: none;
            text-align: center;
            margin-bottom: 28px;
        }
        .mobile-brand .logo-text {
            font-size: 1.5rem;
            font-weight: 800;
            color: var(--brand-red);
        }
        .mobile-brand .tagline-text {
            font-size: .8rem;
            color: var(--ink-muted);
            margin-top: 2px;
        }
        @media (max-width: 768px) {
            .mobile-brand { display: block; }
        }
    </style>

    {{-- Mobile brand header (hidden on desktop) --}}
    <div class="mobile-brand">
        <div class="logo-text">Aku Wae</div>
        <div class="tagline-text">Urusanmu, ben aku wae.</div>
    </div>

    <div class="login-header">
        <h5>Masuk ke akunmu</h5>
        <p>Selamat datang kembali! Masukkan nomor HP dan PIN kamu.</p>
    </div>

    @if($error)
        <div class="login-alert">{{ $error }}</div>
    @endif

    {{-- Phone --}}
    <div class="field">
        <label for="phone_number">Nomor HP</label>
        <div class="field-wrap">
            <span class="field-icon"><i class="fas fa-mobile-alt"></i></span>
            <input
                wire:model="phone_number"
                id="phone_number"
                type="tel"
                placeholder="08xxxxxxxxxx"
                class="{{ $errors->has('phone_number') ? 'is-invalid' : '' }}"
                autocomplete="tel"
            >
        </div>
        @error('phone_number')
            <div class="invalid-msg">{{ $message }}</div>
        @enderror
    </div>

    {{-- PIN --}}
    <div class="field">
        <label for="pin">PIN 6 Digit</label>
        <div class="field-wrap">
            <span class="field-icon"><i class="fas fa-lock"></i></span>
            <input
                wire:model="pin"
                id="pin"
                type="password"
                inputmode="numeric"
                maxlength="6"
                placeholder="••••••"
                class="{{ $errors->has('pin') ? 'is-invalid' : '' }}"
                autocomplete="current-password"
            >
        </div>
        @error('pin')
            <div class="invalid-msg">{{ $message }}</div>
        @enderror
    </div>

    {{-- Submit --}}
    <button
        wire:click="login"
        wire:loading.attr="disabled"
        class="btn-login"
    >

        <span wire:loading.remove>Masuk Sekarang</span>
        <span wire:loading>Memproses...</span>
    </button>

    <div class="login-divider">atau</div>

    <div class="login-register">
        Belum punya akun?
        <a href="{{ route('register') }}">Daftar sekarang</a>
    </div>

</div>