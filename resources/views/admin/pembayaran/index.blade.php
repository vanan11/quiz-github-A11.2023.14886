<x-layouts.app title="Verifikasi Pembayaran">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-slate-800">
            Verifikasi Pembayaran
        </h1>

        <p class="mt-1 text-slate-500">
            Periksa bukti pembayaran pasien sebelum melakukan verifikasi.
        </p>
    </div>

    @if ($errors->any())
        <div class="mb-5 rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700" role="alert">
            <ul class="list-disc pl-5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="overflow-x-auto rounded-2xl border border-slate-200 bg-white shadow-sm">
        <table class="table w-full">
            <thead class="bg-slate-50 text-slate-600">
                <tr>
                    <th>Tagihan</th>
                    <th>Pasien</th>
                    <th>Dokter</th>
                    <th>Tanggal Periksa</th>
                    <th>Jumlah</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>

            <tbody>
                @forelse ($pembayarans as $pembayaran)
                    <tr>
                        <td>#{{ $pembayaran->id }}</td>

                        <td>
                            {{ $pembayaran->periksa?->daftarPoli?->pasien?->nama ?? '-' }}
                        </td>

                        <td>
                            {{ $pembayaran->periksa?->daftarPoli?->jadwalPeriksa?->dokter?->nama ?? '-' }}
                        </td>

                        <td>
                            {{ $pembayaran->periksa?->tgl_periksa?->format('d-m-Y H:i') ?? '-' }}
                        </td>

                        <td class="whitespace-nowrap font-semibold">
                            Rp {{ number_format($pembayaran->jumlah, 0, ',', '.') }}
                        </td>

                        <td>{{ $pembayaran->labelStatus() }}</td>

                        <td>
                            <div class="flex flex-wrap items-center gap-2">
                                @if ($pembayaran->bukti_path)
                                    <a href="{{ route('admin.pembayaran.bukti', ['id' => $pembayaran->id]) }}"
                                        target="_blank"
                                        rel="noopener"
                                        class="rounded-lg bg-slate-100 px-3 py-2 font-semibold text-slate-700 hover:bg-slate-200">
                                        Lihat Bukti
                                    </a>
                                @else
                                    <span class="text-slate-400">
                                        Belum ada bukti
                                    </span>
                                @endif

                                @if ($pembayaran->status === \App\Models\Pembayaran::STATUS_MENUNGGU_VERIFIKASI)
                                    <form method="POST"
                                        action="{{ route('admin.pembayaran.verifikasi', ['id' => $pembayaran->id]) }}">
                                        @csrf

                                        <button type="submit"
                                            class="rounded-lg bg-indigo-600 px-3 py-2 font-semibold text-white hover:bg-indigo-700">
                                            Verifikasi
                                        </button>
                                    </form>
                                @elseif ($pembayaran->status === \App\Models\Pembayaran::STATUS_TERBAYAR)
                                    <span class="text-sm text-emerald-700">
                                        {{ $pembayaran->verifikator?->nama ?? 'Admin' }}
                                        · {{ $pembayaran->diverifikasi_pada?->format('d-m-Y H:i') ?? '-' }}
                                    </span>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="py-8 text-center text-slate-500">
                            Belum ada tagihan pemeriksaan.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-5">
        {{ $pembayarans->links() }}
    </div>
</x-layouts.app>