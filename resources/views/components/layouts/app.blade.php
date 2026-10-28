
<!DOCTYPE html>
<html lang="id" data-theme="light">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>{{ $title ?? 'Poliklinik' }}</title>

    {{-- GOOGLE FONTS --}}
    <link rel="preconnect"
          href="https://fonts.googleapis.com">

    <link
        href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Instrument+Serif:ital@0;1&display=swap"
        rel="stylesheet">

    {{-- FONT AWESOME --}}
    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css">

    {{-- VITE --}}
    @vite([
        'resources/js/app.js',
        'resources/css/app.css'
    ])

</head>

<body>

    <div class="app-wrapper">

        {{-- ====================================== --}}
        {{-- SIDEBAR --}}
        {{-- ====================================== --}}

        <div id="appSidebar" class="sidebar-fixed">

            @include('components.partials.sidebar')

        </div>

        {{-- ====================================== --}}
        {{-- OVERLAY MOBILE --}}
        {{-- ====================================== --}}

        <div
            class="sidebar-overlay"
            id="sidebarOverlay"
            onclick="toggleSidebar()">
        </div>

        {{-- ====================================== --}}
        {{-- MAIN CONTENT --}}
        {{-- ====================================== --}}

        <div class="main-content">

            {{-- HEADER / NAVBAR --}}
            @include('components.partials.header')

            {{-- AREA KONTEN --}}
            <div class="main-scroll">

                {{-- ================================== --}}
                {{-- NOTIFIKASI SUKSES --}}
                {{-- ================================== --}}

                @if(session('success'))

                    <div class="alert alert-success
                                mb-4 rounded-xl shadow-sm">

                        <i class="fas fa-check-circle"></i>

                        <span>
                            {{ session('success') }}
                        </span>

                    </div>

                @endif

                {{-- ================================== --}}
                {{-- NOTIFIKASI ERROR --}}
                {{-- ================================== --}}

                @if(session('error'))

                    <div class="alert alert-error
                                mb-4 rounded-xl shadow-sm">

                        <i class="fas fa-circle-xmark"></i>

                        <span>
                            {{ session('error') }}
                        </span>

                    </div>

                @endif

                {{-- ================================== --}}
                {{-- KONTEN HALAMAN --}}
                {{-- ================================== --}}

                {{ $slot }}

            </div>

            {{-- ====================================== --}}
            {{-- FOOTER --}}
            {{-- ====================================== --}}

            @include('components.partials.footer')

        </div>

    </div>

    {{-- ========================================== --}}
    {{-- JAVASCRIPT SIDEBAR --}}
    {{-- ========================================== --}}

    <script>

        function toggleSidebar() {

            const sidebar =
                document.getElementById('appSidebar');

            const overlay =
                document.getElementById('sidebarOverlay');

            if (!sidebar || !overlay) {
                return;
            }

            sidebar.classList.toggle('open');

            overlay.style.display =
                sidebar.classList.contains('open')
                    ? 'block'
                    : 'none';
        }

        // ========================================
        // FULLSCREEN
        // ========================================

        function toggleFullscreen() {

            const icon =
                document.getElementById('fsIcon');

            if (!document.fullscreenElement) {

                document.documentElement.requestFullscreen();

                if (icon) {
                    icon.className = 'fas fa-compress';
                }

            } else {

                document.exitFullscreen();

                if (icon) {
                    icon.className = 'fas fa-expand';
                }
            }
        }

        // ========================================
        // UPDATE ICON FULLSCREEN
        // ========================================

        document.addEventListener(
            'fullscreenchange',
            function () {

                const icon =
                    document.getElementById('fsIcon');

                if (!icon) {
                    return;
                }

                icon.className =
                    document.fullscreenElement
                        ? 'fas fa-compress'
                        : 'fas fa-expand';
            }
        );

    </script>

    @stack('scripts')

</body>

</html>
