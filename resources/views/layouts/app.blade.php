<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="vapid-public-key" content="{{ config('webpush.vapid.public_key') }}">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700;800&display=swap" rel="stylesheet">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#dc3545">
    <link rel="manifest" href="/manifest.json">
    <link rel="apple-touch-icon" href="/images/icons/icon-192x192.png">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">

    <title>{{ $title ?? 'ToLong App' }}</title>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet-control-geocoder@2.4.0/dist/Control.Geocoder.css" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    <style>
        * { -webkit-tap-highlight-color: transparent; }
        body, p, h1, h2, h3, h4, h5, h6, label, input, button, a, span:not(.fa):not(.fas):not(.far):not(.fab) {
            font-family: 'Poppins', sans-serif;
        }

        body {
            background: #f5f5f5;
            max-width: 480px;
            margin: 0 auto;
            min-height: 100vh;
            position: relative;
        }

        /* Area konten utama — beri ruang untuk topbar & bottom nav */
        .app-content {
            padding-top: 56px;
            padding-bottom: 70px;
            min-height: 100vh;
        }

        /* Top bar */
        .app-topbar {
            position: fixed;
            top: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 100%;
            max-width: 480px;
            height: 56px;
            background: #fff;
            border-bottom: 1px solid #f0f0f0;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 16px;
            z-index: 100;
        }

        .leaflet-control-geocoder-form input {
            font-size: 13px;
        }

        .leaflet-control-geocoder-alternatives {
            max-height: 200px;
            overflow-y: auto;
            z-index: 10000 !important;
        }
        .leaflet-control-geocoder {
            z-index: 10000 !important;
        }

        /* Bottom navigation */
        .app-bottomnav {
            position: fixed;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 100%;
            max-width: 480px;
            height: 62px;
            background: #fff;
            border-top: 1px solid #f0f0f0;
            display: flex;
            align-items: center;
            justify-content: space-around;
            z-index: 100;
        }

        .bottomnav-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 2px;
            text-decoration: none;
            color: #adb5bd;
            font-size: 10px;
            font-weight: 500;
            padding: 6px 16px;
            transition: color 0.15s;
        }

        .bottomnav-item.active { color: #dc3545; }
        .bottomnav-item .icon { font-size: 22px; line-height: 1; }

        /* Card style */
        .app-card {
            background: #fff;
            border-radius: 16px;
            padding: 16px;
            margin-bottom: 12px;
            box-shadow: 0 1px 4px rgba(0,0,0,0.06);
        }

        /* Hapus border default Bootstrap */
        .form-control, .form-select {
            border-color: #e9ecef;
        }
        .form-control:focus, .form-select:focus {
            border-color: #dc3545;
            box-shadow: 0 0 0 3px rgba(220,53,69,0.1);
        }
    </style>
</head>
<body>

    {{-- Top Bar --}}
    <div class="app-topbar">
        <span class="fw-bold text-danger fs-5">
            {{ $title ?? 'ToLong' }}
        </span>
        <form action="{{ route('logout') }}" method="POST" class="mb-0">
            @csrf
            <button class="btn btn-sm btn-outline-secondary rounded-3 py-1 px-2"
                style="font-size:12px;">
                Keluar
            </button>
        </form>
    </div>

    {{-- Konten Utama --}}
    <main class="app-content">
        {{ $slot }}
    </main>

    {{-- Bottom Navigation (dinamis per role) --}}
    @auth
        @if(auth()->user()->role === 'tenant')
            <nav class="app-bottomnav">
                <a href="{{ route('tenant.dashboard') }}"
                    class="bottomnav-item {{ request()->routeIs('tenant.dashboard') ? 'active' : '' }}">
                    <i class="fas fa-home icon"></i> Beranda
                </a>
                <a href="{{ route('tenant.menu.index') }}"
                    class="bottomnav-item {{ request()->routeIs('tenant.menu.*') ? 'active' : '' }}">
                    <i class="fas fa-utensils icon"></i> Menu
                </a>
                <a href="{{ route('tenant.order.index') }}"
                    class="bottomnav-item {{ request()->routeIs('tenant.order.*') ? 'active' : '' }}">
                    <i class="fas fa-box icon"></i> Order
                </a>
                <a href="{{ route('tenant.report') }}"
                    class="bottomnav-item {{ request()->routeIs('tenant.report.*') ? 'active' : '' }}">
                    <i class="fas fa-chart-bar icon"></i> Laporan
                </a>
                <a href="{{ route('tenant.settings') }}"
                    class="bottomnav-item {{ request()->routeIs('tenant.settings') ? 'active' : '' }}">
                    <i class="fas fa-cog icon"></i> Toko
                </a>
            </nav>
        @elseif(auth()->user()->role === 'customer')
            <nav class="app-bottomnav">
                <a href="{{ route('customer.dashboard') }}"
                    class="bottomnav-item {{ request()->routeIs('customer.dashboard') ? 'active' : '' }}">
                    <i class="fas fa-home icon"></i> Beranda
                </a>
                <a href="{{ route('customer.order.index') }}"
                    class="bottomnav-item {{ request()->routeIs('customer.order.*') ? 'active' : '' }}">
                    <i class="fas fa-list icon"></i> Order
                </a>
                <a href="#"
                    class="bottomnav-item">
                    <i class="fas fa-credit-card icon"></i> Dompet
                </a>
                <a href="{{ route('customer.profile') }}"
                    class="bottomnav-item {{ request()->routeIs('customer.profile') ? 'active' : '' }}">
                    <i class="fas fa-user icon"></i> Profil
                </a>
            </nav>
        @elseif(auth()->user()->role === 'driver')
            <nav class="app-bottomnav">
                <a href="{{ route('driver.dashboard') }}"
                    class="bottomnav-item {{ request()->routeIs('driver.dashboard') ? 'active' : '' }}">
                    <i class="fas fa-home icon"></i> Beranda
                </a>
                <a href="{{ route('driver.order.history') }}"
                    class="bottomnav-item {{ request()->routeIs('driver.order.history') ? 'active' : '' }}">
                    <i class="fas fa-clock icon"></i> Riwayat
                </a>
                <a href="{{ route('driver.profile') }}" class="bottomnav-item">
                    <i class="fas fa-user icon"></i> Profil
                </a>
            </nav>
        @endif
    @endauth

    {{-- Global Popup Components (tersedia di semua halaman) --}}
    @auth
        @if(auth()->user()->role === 'tenant')
            @livewire('tenant.incoming-order')
        @elseif(auth()->user()->role === 'driver')
            @livewire('driver.incoming-order')
        @endif
    @endauth

    @livewireScripts
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

    {{-- Global Echo Listeners (selalu aktif di semua halaman) --}}
    @auth
        @if(auth()->user()->role === 'tenant')
            <script>
            document.addEventListener('DOMContentLoaded', function () {
                function initTenantEcho() {
                    if (typeof window.Echo === 'undefined') {
                        setTimeout(initTenantEcho, 300);
                        return;
                    }

                    const tenantId = '{{ auth()->user()->tenant->id }}';

                    window.Echo.private('tenant.' + tenantId)
                        .listen('.order.waiting', (data) => {
                            console.log('Order masuk ke tenant:', data);
                            Livewire.dispatch('incoming-order-tenant', [data]);
                        })
                        .listen('.status.updated', (data) => {
                            console.log('Status order berubah:', data);
                            Livewire.dispatch('refresh-active-orders');
                        });
                }

                initTenantEcho();
            });
            </script>
        @elseif(auth()->user()->role === 'driver')
            <script>
            document.addEventListener('DOMContentLoaded', function () {
                function initDriverEcho() {
                    if (typeof window.Echo === 'undefined') {
                        setTimeout(initDriverEcho, 300);
                        return;
                    }

                    // Listen order masuk (semua driver)
                    window.Echo.channel('drivers.orders')
                        .listen('.order.created', (data) => {
                            console.log('Order masuk:', data);
                            Livewire.dispatch('incoming-order', [data]);
                        });

                    const driverId = '{{ auth()->user()->driver?->id ?? '' }}';

                    // Listen notif khusus driver ini
                    window.Echo.private('driver.' + driverId)
                        .listen('.order.ready', (data) => {
                            console.log('Makanan siap:', data);
                            Livewire.dispatch('order-ready-notify', [data]);
                        })
                        .listen('.order.mismatch-resolved', (data) => {
                            console.log('Mismatch resolved:', data);
                            Livewire.dispatch('order-mismatch-resolved', [data]);
                        });
                }

                initDriverEcho();
            });
            </script>
        @endif
    @endauth

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://unpkg.com/leaflet-control-geocoder@2.4.0/dist/Control.Geocoder.js"></script>

    @stack('scripts')

    <script>
    if ('serviceWorker' in navigator) {
        window.addEventListener('load', function () {
            navigator.serviceWorker.register('/sw.js')
                .then(reg => {
                    console.log('[SW] Registered:', reg.scope);
                    initPushNotification(reg);
                })
                .catch(err => console.error('[SW] Registration failed:', err));
        });
    }

    function urlBase64ToUint8Array(base64String) {
        const padding = '='.repeat((4 - base64String.length % 4) % 4);
        const base64 = (base64String + padding).replace(/-/g, '+').replace(/_/g, '/');
        const rawData = window.atob(base64);
        return Uint8Array.from([...rawData].map(char => char.charCodeAt(0)));
    }

    async function initPushNotification(registration) {
        @auth
        // Cek apakah browser support push
        if (!('PushManager' in window)) {
            console.log('[Push] Not supported');
            return;
        }

        // Cek izin notifikasi
        let permission = Notification.permission;

        if (permission === 'default') {
            permission = await Notification.requestPermission();
        }

        if (permission !== 'granted') {
            console.log('[Push] Permission denied');
            return;
        }

        // Cek subscription yang sudah ada
        let subscription = await registration.pushManager.getSubscription();

        if (!subscription) {
            const vapidKey = document.querySelector('meta[name="vapid-public-key"]').content;

            subscription = await registration.pushManager.subscribe({
                userVisibleOnly: true,
                applicationServerKey: urlBase64ToUint8Array(vapidKey),
            });
        }

        // Kirim subscription ke server
        const subData = subscription.toJSON();

        await fetch('{{ route('push.subscribe') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}',
            },
            body: JSON.stringify({
                endpoint: subData.endpoint,
                keys: subData.keys,
            }),
        });

        console.log('[Push] Subscribed successfully');
        @endauth
    }
    </script>
</body>
</html>