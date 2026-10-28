@auth
    <details {{ $attributes->merge(['class' => 'relative']) }} data-notifikasi-menu>
        <summary class="btn btn-square btn-ghost relative list-none cursor-pointer [&::-webkit-details-marker]:hidden"
            aria-label="Notifikasi, {{ $jumlahBelumDibaca }} belum dibaca">
            <i class="fas fa-bell text-lg" aria-hidden="true"></i>

            @if ($jumlahBelumDibaca > 0)
                <span class="absolute -right-1 -top-1 min-w-5 rounded-full bg-red-500 px-1.5 py-0.5 text-center text-[10px] font-bold text-white"
                    aria-hidden="true">
                    {{ $jumlahBelumDibaca > 99 ? '99+' : $jumlahBelumDibaca }}
                </span>
            @endif
        </summary>

        <div class="fixed inset-x-3 top-16 z-50 overflow-hidden rounded-2xl border border-slate-200 bg-white text-slate-700 shadow-xl sm:absolute sm:inset-x-auto sm:right-0 sm:top-full sm:mt-2 sm:w-96">
            <div class="flex items-center justify-between gap-3 border-b border-slate-100 p-4">
                <div>
                    <p class="font-bold text-slate-800">Notifikasi</p>
                    <p class="text-xs text-slate-500">
                        {{ $jumlahBelumDibaca }} belum dibaca
                    </p>
                </div>

                @if ($jumlahBelumDibaca > 0)
                    <form method="POST" action="{{ route('notifikasi.baca-semua') }}">
                        @csrf

                        <button type="submit"
                            class="text-xs font-semibold text-indigo-700 hover:underline">
                            Tandai semua dibaca
                        </button>
                    </form>
                @endif
            </div>

            <div class="max-h-80 divide-y divide-slate-100 overflow-y-auto">
                @forelse ($notifikasis as $notifikasi)
                    <form method="POST"
                        action="{{ route('notifikasi.buka', ['id' => $notifikasi->id]) }}">
                        @csrf

                        <button type="submit"
                            class="block w-full p-4 text-left hover:bg-slate-100 {{ $notifikasi->read_at === null ? 'bg-indigo-50' : 'bg-white' }}">
                            <span class="flex items-start justify-between gap-3">
                                <span class="text-sm font-semibold text-slate-800">
                                    {{ $notifikasi->data['judul'] ?? 'Notifikasi' }}
                                </span>

                                <span class="shrink-0 text-[10px] text-indigo-700">
                                    {{ $notifikasi->read_at === null ? 'Belum dibaca' : 'Sudah dibaca' }}
                                </span>
                            </span>

                            <span class="mt-1 block line-clamp-2 text-sm text-slate-600">
                                {{ $notifikasi->data['pesan'] ?? '' }}
                            </span>

                            <span class="mt-2 block text-xs text-slate-400">
                                {{ $notifikasi->created_at?->format('d-m-Y H:i') ?? '-' }}
                            </span>
                        </button>
                    </form>
                @empty
                    <p class="p-6 text-center text-sm text-slate-500">
                        Belum ada notifikasi.
                    </p>
                @endforelse
            </div>

            <a href="{{ route('notifikasi.index') }}"
                class="block border-t border-slate-100 p-3 text-center text-sm font-semibold text-indigo-700 hover:bg-slate-50">
                Lihat semua notifikasi
            </a>
        </div>
    </details>

    @pushOnce('scripts', 'lonceng-notifikasi')
        <script>
            document.addEventListener('click', (event) => {
                document.querySelectorAll('[data-notifikasi-menu][open]').forEach((menu) => {
                    if (!menu.contains(event.target)) {
                        menu.open = false;
                    }
                });
            });

            document.addEventListener('keydown', (event) => {
                if (event.key === 'Escape') {
                    document.querySelectorAll('[data-notifikasi-menu][open]').forEach((menu) => {
                        menu.open = false;
                        menu.querySelector('summary').focus();
                    });
                }
            });
        </script>
    @endPushOnce
@endauth