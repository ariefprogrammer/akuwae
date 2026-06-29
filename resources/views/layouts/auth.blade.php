<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700;800&display=swap" rel="stylesheet">
    <title>Aku Wae Super App</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    <style>
        /* ── Palette ──────────────────────────────────────────── */
        :root {
            --brand-red:    #E8200C;
            --brand-red-dk: #B01808;
            --brand-red-lt: #FFF1F0;
            --surface:      #FFFFFF;
            --ink:          #111111;
            --ink-muted:    #6B7280;
            --border:       #E5E7EB;
        }

        body, p, h1, h2, h3, h4, h5, h6, label, input, button, a, span:not(.fa):not(.fas):not(.far):not(.fab) {
            font-family: 'Poppins', sans-serif;
        }

        /* ── Layout shell ─────────────────────────────────────── */
        .auth-shell {
            display: flex;
            min-height: 100dvh;
        }

        /* ── LEFT panel — branding ────────────────────────────── */
        .auth-brand {
            flex: 1 1 55%;
            background: var(--brand-red);
            background-image:
                radial-gradient(ellipse 80% 60% at 20% 110%, rgba(0,0,0,.25) 0%, transparent 70%),
                radial-gradient(ellipse 60% 50% at 90% -10%, rgba(255,255,255,.12) 0%, transparent 60%);
            color: #fff;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            padding: 48px 56px;
            position: relative;
            overflow: hidden;
        }

        /* decorative circle */
        .auth-brand::after {
            content: '';
            position: absolute;
            width: 480px;
            height: 480px;
            border-radius: 50%;
            border: 60px solid rgba(255,255,255,.07);
            bottom: -160px;
            right: -120px;
            pointer-events: none;
        }

        .brand-logo {
            font-size: 2rem;
            font-weight: 800;
            letter-spacing: -.5px;
            line-height: 1;
        }
        .brand-logo span { opacity: .65; font-weight: 400; }

        .brand-tagline {
            font-size: 2.5rem;
            font-weight: 700;
            line-height: 1.15;
            max-width: 400px;
            margin-top: auto;
        }
        .brand-tagline em {
            font-style: normal;
            background: rgba(255,255,255,.2);
            border-radius: 6px;
            padding: 0 6px;
        }

        /* service cards */
        .service-list {
            display: flex;
            flex-direction: column;
            gap: 12px;
            margin-top: 40px;
            position: relative;
            z-index: 1;
        }
        .service-item {
            display: flex;
            align-items: center;
            gap: 14px;
            background: rgba(255,255,255,.12);
            border: 1px solid rgba(255,255,255,.2);
            border-radius: 14px;
            padding: 14px 18px;
            backdrop-filter: blur(6px);
        }
        .service-icon {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            background: rgba(255,255,255,.25);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
            flex-shrink: 0;
        }
        .service-name {
            font-weight: 700;
            font-size: .95rem;
            line-height: 1.2;
        }
        .service-desc {
            font-size: .8rem;
            opacity: .75;
            margin-top: 2px;
        }

        .brand-footer {
            font-size: .75rem;
            opacity: .5;
            margin-top: 48px;
        }

        /* ── RIGHT panel — form slot ──────────────────────────── */
        .auth-form-panel {
            flex: 0 0 420px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 40px 32px;
            background: #FAFAFA;
        }

        .auth-form-panel > div { width: 100%; }

        /* ── Responsive — stack on mobile ─────────────────────── */
        @media (max-width: 768px) {
            .auth-shell     { flex-direction: column; }
            .auth-brand     { flex: none; padding: 32px 24px; }
            .brand-tagline  { font-size: 1.75rem; }
            .auth-form-panel { flex: none; padding: 32px 20px; }
            .service-list   { flex-direction: row; gap: 8px; flex-wrap: wrap; }
            .service-item   { flex: 1 1 calc(50% - 4px); }
        }

        @media (max-width: 480px) {
            .service-item { flex: 1 1 100%; }
        }
    </style>
</head>
<body>

<div class="auth-shell">

    {{-- ── LEFT: Branding ── --}}
    <div class="auth-form-panel">
        {{ $slot }}
    </div>

    {{-- ── RIGHT: Form slot ── --}}
    <div class="auth-brand">
        <div class="brand-logo">Aku Wae <span>•</span></div>

        <div>
            <div class="brand-tagline">
                Urusanmu,<br><em>ben aku wae.</em>
            </div>
            <div class="col-12 mt-1 p-2">
                <p>Aku Wae adalah layanan on demand yang membantu masyarakat untuk menyelesaikan banyak persoalan kehidupan sehari-hari. Gunakan layanan Aku Wae untuk meningkatkan kualitas hidup anda dan membuat semua menjadi "Mudah".</p>
            </div>

            <div class="service-list">
                <div class="service-item">
                    <div class="service-icon"><i class="fas fa-box-open text-light"></i></div>
                    <div>
                        <div class="service-name">Aku Bantu</div>
                        <div class="service-desc">Minta tolong apapun ke driver kami. Belikan bensin ketika mogok? antar barang? belikan obat? Semua OKE</div>
                    </div>
                </div>
                <div class="service-item">
                    <div class="service-icon"><i class="fas fa-utensils text-light"></i></div>
                    <div>
                        <div class="service-name">Aku Makan</div>
                        <div class="service-desc">Pesan makanan dari ratusan tenant. Tidak perlu keluar rumah, biar driver Aku Wae yang urus semuanya.</div>
                    </div>
                </div>
                <div class="service-item">
                    <div class="service-icon"><i class="fas fa-motorcycle text-light"></i></div>
                    <div>
                        <div class="service-name">Aku Antar</div>
                        <div class="service-desc">Ojek cepat, sampai tujuanmu. Tinggal pesan di aplikasi, kami jemput dan antar sesuai lokasi pilihan anda.</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="brand-footer">© {{ date('Y') }}  Aku Wae. All right reserved.</div>
    </div>

</div>

@livewireScripts
</body>
</html>