
<x-layouts.app title="Profile">

    {{-- ====================================== --}}
    {{-- HEADER --}}
    {{-- ====================================== --}}

    <div class="flex items-center gap-3 mb-6">

        <div class="w-11 h-11 rounded-xl
                    bg-primary/10 text-primary
                    flex items-center justify-center">

            <i class="fas fa-user-gear text-lg"></i>

        </div>

        <div>
            <h2 class="text-2xl font-bold text-slate-800">
                Profile Saya
            </h2>

            <p class="text-sm text-slate-500 mt-1">
                Informasi akun dan pengaturan keamanan.
            </p>
        </div>

    </div>

    {{-- ====================================== --}}
    {{-- INFORMASI PROFILE --}}
    {{-- ====================================== --}}

    <div class="card bg-base-100 shadow-md
                rounded-2xl border border-slate-200 mb-6">

        <div class="card-body p-6 md:p-8">

            <div class="flex items-center gap-4 mb-6">

                <div class="w-14 h-14 rounded-full
                            bg-primary text-white
                            flex items-center justify-center
                            text-xl font-bold">

                    {{ strtoupper(substr($user->nama ?? 'U', 0, 1)) }}

                </div>

                <div>
                    <h3 class="text-lg font-bold text-slate-800">
                        {{ $user->nama }}
                    </h3>

                    <span class="inline-flex px-3 py-1 mt-1
                                 bg-blue-100 text-blue-700
                                 rounded-full text-xs font-semibold">

                        {{ ucfirst($user->role) }}

                    </span>
                </div>

            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">

                {{-- Nama --}}
                <div>
                    <p class="text-xs font-semibold
                              text-slate-400 uppercase mb-1">
                        Nama Lengkap
                    </p>

                    <p class="text-sm font-medium text-slate-800">
                        {{ $user->nama ?? '-' }}
                    </p>
                </div>

                {{-- Email --}}
                <div>
                    <p class="text-xs font-semibold
                              text-slate-400 uppercase mb-1">
                        Email
                    </p>

                    <p class="text-sm font-medium text-slate-800
                              break-all">
                        {{ $user->email ?? '-' }}
                    </p>
                </div>

                {{-- Nomor HP --}}
                <div>
                    <p class="text-xs font-semibold
                              text-slate-400 uppercase mb-1">
                        Nomor HP
                    </p>

                    <p class="text-sm font-medium text-slate-800">
                        {{ $user->no_hp ?? '-' }}
                    </p>
                </div>

                {{-- Role --}}
                <div>
                    <p class="text-xs font-semibold
                              text-slate-400 uppercase mb-1">
                        Hak Akses
                    </p>

                    <p class="text-sm font-medium text-slate-800">
                        {{ ucfirst($user->role) }}
                    </p>
                </div>

            </div>

        </div>

    </div>

    {{-- ====================================== --}}
    {{-- FORM GANTI PASSWORD --}}
    {{-- ====================================== --}}

    <div class="card bg-base-100 shadow-md
                rounded-2xl border border-slate-200">

        <div class="card-body p-6 md:p-8">

            {{-- Judul Form --}}
            <div class="flex items-center gap-3 mb-6">

                <div class="w-10 h-10 rounded-xl
                            bg-amber-100 text-amber-600
                            flex items-center justify-center">

                    <i class="fas fa-lock"></i>

                </div>

                <div>
                    <h3 class="text-lg font-bold text-slate-800">
                        Ganti Password
                    </h3>

                    <p class="text-sm text-slate-500 mt-1">
                        Perbarui password untuk keamanan akun.
                    </p>
                </div>

            </div>

            <form action="{{ route('profile.password.update') }}"
                  method="POST">

                @csrf
                @method('PUT')

                {{-- ================================== --}}
                {{-- PASSWORD LAMA --}}
                {{-- ================================== --}}

                <div class="mb-5">

                    <label for="current_password"
                        class="block text-sm font-semibold
                               text-slate-700 mb-2">

                        Password Lama
                        <span class="text-red-500">*</span>

                    </label>

                    <div class="relative">

                        <input
                            type="password"
                            id="current_password"
                            name="current_password"
                            autocomplete="current-password"
                            placeholder="Masukkan password lama"
                            class="w-full px-4 py-3 pr-12
                                   border-2 rounded-xl
                                   focus:border-primary
                                   focus:outline-none
                                   @error('current_password')
                                       border-red-500
                                   @enderror"
                            required>

                        <button
                            type="button"
                            onclick="togglePassword('current_password', this)"
                            class="absolute right-4 top-1/2
                                   -translate-y-1/2 text-slate-400
                                   hover:text-slate-700"
                            aria-label="Tampilkan password lama">

                            <i class="fas fa-eye"></i>

                        </button>

                    </div>

                    @error('current_password')
                        <p class="text-xs text-red-500 mt-2">
                            <i class="fas fa-circle-exclamation mr-1"></i>
                            {{ $message }}
                        </p>
                    @enderror

                </div>

                {{-- ================================== --}}
                {{-- PASSWORD BARU --}}
                {{-- ================================== --}}

                <div class="mb-5">

                    <label for="password"
                        class="block text-sm font-semibold
                               text-slate-700 mb-2">

                        Password Baru
                        <span class="text-red-500">*</span>

                    </label>

                    <div class="relative">

                        <input
                            type="password"
                            id="password"
                            name="password"
                            autocomplete="new-password"
                            placeholder="Masukkan password baru"
                            minlength="8"
                            class="w-full px-4 py-3 pr-12
                                   border-2 rounded-xl
                                   focus:border-primary
                                   focus:outline-none
                                   @error('password')
                                       border-red-500
                                   @enderror"
                            required>

                        <button
                            type="button"
                            onclick="togglePassword('password', this)"
                            class="absolute right-4 top-1/2
                                   -translate-y-1/2 text-slate-400
                                   hover:text-slate-700"
                            aria-label="Tampilkan password baru">

                            <i class="fas fa-eye"></i>

                        </button>

                    </div>

                    <p class="text-xs text-slate-400 mt-2">
                        Minimal 8 karakter dan berbeda
                        dari password lama.
                    </p>

                    @error('password')
                        <p class="text-xs text-red-500 mt-2">
                            <i class="fas fa-circle-exclamation mr-1"></i>
                            {{ $message }}
                        </p>
                    @enderror

                </div>

                {{-- ================================== --}}
                {{-- KONFIRMASI PASSWORD BARU --}}
                {{-- ================================== --}}

                <div class="mb-8">

                    <label for="password_confirmation"
                        class="block text-sm font-semibold
                               text-slate-700 mb-2">

                        Konfirmasi Password Baru
                        <span class="text-red-500">*</span>

                    </label>

                    <div class="relative">

                        <input
                            type="password"
                            id="password_confirmation"
                            name="password_confirmation"
                            autocomplete="new-password"
                            placeholder="Ulangi password baru"
                            minlength="8"
                            class="w-full px-4 py-3 pr-12
                                   border-2 rounded-xl
                                   focus:border-primary
                                   focus:outline-none
                                   @error('password_confirmation')
                                       border-red-500
                                   @enderror"
                            required>

                        <button
                            type="button"
                            onclick="togglePassword('password_confirmation', this)"
                            class="absolute right-4 top-1/2
                                   -translate-y-1/2 text-slate-400
                                   hover:text-slate-700"
                            aria-label="Tampilkan konfirmasi password">

                            <i class="fas fa-eye"></i>

                        </button>

                    </div>

                    @error('password_confirmation')
                        <p class="text-xs text-red-500 mt-2">
                            <i class="fas fa-circle-exclamation mr-1"></i>
                            {{ $message }}
                        </p>
                    @enderror

                </div>

                {{-- ================================== --}}
                {{-- TOMBOL SIMPAN --}}
                {{-- ================================== --}}

                <div class="flex flex-wrap gap-3">

                    <button
                        type="submit"
                        class="inline-flex items-center gap-2
                               px-6 py-3 rounded-xl
                               bg-primary hover:bg-primary/90
                               text-white text-sm font-semibold
                               transition">

                        <i class="fas fa-key"></i>
                        Simpan Password Baru

                    </button>

                    <a href="{{ url()->previous() }}"
                        class="inline-flex items-center gap-2
                               px-6 py-3 rounded-xl
                               bg-slate-100 hover:bg-slate-200
                               text-slate-600 text-sm font-semibold
                               transition">

                        Batal

                    </a>

                </div>

            </form>

        </div>

    </div>

    {{-- ====================================== --}}
    {{-- SHOW / HIDE PASSWORD --}}
    {{-- ====================================== --}}

    <script>
        function togglePassword(inputId, button) {

            const input = document.getElementById(inputId);
            const icon = button.querySelector('i');

            if (!input || !icon) return;

            if (input.type === 'password') {

                input.type = 'text';

                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');

                button.setAttribute(
                    'aria-label',
                    'Sembunyikan password'
                );

            } else {

                input.type = 'password';

                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');

                button.setAttribute(
                    'aria-label',
                    'Tampilkan password'
                );
            }
        }
    </script>

</x-layouts.app>
