<x-layouts.app title="Notifikasi">
    <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">
                Notifikasi
            </h1>

            <p class="mt-1 text-slate-500">
                {{ $jumlahBelumDibaca }} notifikasi belum dibaca.
            </p>
        </div>

        @if ($jumlahBelumDibaca > 0)
            <form method="POST" action="{{ route('notifikasi.baca-semua') }}">
                @csrf

                <button type="submit"
                    class="rounded-lg bg-indigo-600 px-4 py-2 font-semibold text-white hover:bg-indigo-700">
                    Tandai semua dibaca
                </button>
            </form>
        @endif
    </div>

    <div class="flex flex-col gap-4">
        @forelse ($notifikasis as $notifikasi)
            <article class="rounded-2xl border bg-white p-5 shadow-sm {{ $notifikasi->read_at === null ? 'border-indigo-200' : 'border-slate-200' }}">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <h2 class="font-bold text-slate-800">
                        {{ $notifikasi->data['judul'] ?? 'Notifikasi' }}
                    </h2>

                    <span class="rounded-lg px-3 py-1 text-xs {{ $notifikasi->read_at === null ? 'bg-indigo-50 text-indigo-700' : 'bg-slate-100 text-slate-500' }}">
                        {{ $notifikasi->read_at === null ? 'Belum dibaca' : 'Sudah dibaca' }}
                    </span>
                </div>

                <p class="mt-2 text-slate-600">
                    {{ $notifikasi->data['pesan'] ?? '' }}
                </p>

                <div class="mt-4 flex flex-wrap items-center justify-between gap-3">
                    <p class="text-sm text-slate-400">
                        {{ $notifikasi->created_at?->format('d-m-Y H:i') ?? '-' }}
                    </p>

                    <form method="POST"
                        action="{{ route('notifikasi.buka', ['id' => $notifikasi->id]) }}">
                        @csrf

                        <button type="submit"
                            class="rounded-lg bg-indigo-50 px-4 py-2 text-sm font-semibold text-indigo-700 hover:bg-indigo-100">
                            Buka notifikasi
                        </button>
                    </form>
                </div>
            </article>
        @empty
            <p class="rounded-2xl border border-slate-200 bg-white p-8 text-center text-slate-500">
                Belum ada notifikasi.
            </p>
        @endforelse
    </div>

    <div class="mt-5">
        {{ $notifikasis->links() }}
    </div>
</x-layouts.app>