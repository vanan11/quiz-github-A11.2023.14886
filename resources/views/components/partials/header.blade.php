<header class="bg-base-100 border-b border-base-300 h-16
               flex items-center px-6 gap-4 sticky top-0 z-30 shadow-sm">

    {{-- ========================================= --}}
    {{-- TOMBOL MENU MOBILE --}}
    {{-- ========================================= --}}

    <button
        type="button"
        onclick="toggleSidebar()"
        class="btn btn-square btn-ghost lg:hidden"
        aria-label="Buka menu">

        <i data-lucide="menu" class="w-5 h-5"></i>

    </button>

    {{-- ========================================= --}}
    {{-- JUDUL HALAMAN --}}
    {{-- ========================================= --}}

    <div class="flex-1 min-w-0">

        <div class="flex items-center gap-2 text-sm">

            <span class="text-base-content/50">
                Poliklinik
            </span>

            <i data-lucide="chevron-right"
               class="w-4 h-4 text-base-content/30"></i>

            <span class="font-semibold text-base-content truncate">
                {{ $title ?? 'Dashboard' }}
            </span>

        </div>

    </div>

    {{-- ========================================= --}}
    {{-- LONCENG NOTIFIKASI --}}
    {{-- ========================================= --}}

    <x-lonceng-notifikasi />

    {{-- ========================================= --}}
    {{-- TOMBOL FULLSCREEN --}}
    {{-- ========================================= --}}

    <button
        type="button"
        onclick="toggleFullscreen()"
        class="btn btn-square btn-ghost"
        aria-label="Mode layar penuh">

        <i id="fsIcon"
           class="fas fa-expand w-5 h-5"></i>

    </button>

    {{-- ========================================= --}}
    {{-- DROPDOWN PROFILE PENGGUNA --}}
    {{-- ========================================= --}}

    @auth

    <details class="dropdown dropdown-end">

        {{-- Tombol Avatar --}}
        <summary
            class="list-none flex items-center gap-3
                   cursor-pointer rounded-xl px-2 py-1
                   hover:bg-base-200 transition
                   focus:outline-none
                   focus-visible:ring-2
                   focus-visible:ring-primary">

            {{-- Nama dan Role --}}
            <div class="text-right hidden sm:block">

                <div class="text-sm font-semibold
                            leading-tight max-w-36 truncate">

                    {{ auth()->user()->nama ?? 'Pengguna' }}

                </div>

                <div class="text-xs text-base-content/50 leading-tight">

                    {{ ucfirst(auth()->user()->role ?? 'Pengguna') }}

                </div>

            </div>

            {{-- Avatar --}}
            <div class="avatar">

                <div class="w-10 h-10 rounded-full
                            bg-primary text-primary-content
                            flex items-center justify-center">

                    <span class="text-sm font-semibold leading-none">

                        {{
                            \Illuminate\Support\Str::upper(
                                \Illuminate\Support\Str::substr(
                                    auth()->user()->nama ?? 'U',
                                    0,
                                    1
                                )
                            )
                        }}

                    </span>

                </div>

            </div>

            {{-- Ikon Dropdown --}}
            <i class="fas fa-chevron-down
                      text-xs text-base-content/50
                      hidden sm:block"></i>

        </summary>

        {{-- =================================== --}}
        {{-- ISI DROPDOWN --}}
        {{-- =================================== --}}

        <ul class="dropdown-content menu bg-base-100
                   rounded-xl z-50 w-60 p-2 mt-3
                   shadow-xl border border-base-300">

            {{-- Informasi Akun --}}
            <li class="menu-title px-3 py-2">

                <span class="text-xs font-semibold text-base-content/60">
                    AKUN SAYA
                </span>

            </li>

            {{-- Nama Pengguna --}}
            <li class="px-3 py-2">

                <div class="flex flex-col gap-1">

                    <span class="font-semibold text-sm text-base-content">
                        {{ auth()->user()->nama ?? 'Pengguna' }}
                    </span>

                    <span class="text-xs text-base-content/50
                                 break-all">

                        {{ auth()->user()->email ?? '-' }}

                    </span>

                </div>

            </li>

            <div class="divider my-1"></div>

            {{-- Menu Profile --}}
            <li>

                <a href="{{ route('profile.show') }}"
                   class="flex items-center gap-3
                          rounded-lg px-3 py-3
                          hover:bg-primary/10
                          hover:text-primary
                          transition
                          {{ request()->routeIs('profile.*')
                              ? 'bg-primary/10 text-primary font-semibold'
                              : '' }}">

                    <i class="fas fa-user-gear text-base"></i>

                    <span>
                        Profile Saya
                    </span>

                </a>

            </li>

        </ul>

    </details>

    @endauth

</header>
