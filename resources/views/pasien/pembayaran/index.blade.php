<x-layouts.app title="Pembayaran">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-slate-800">
            Pembayaran
        </h1>

        <p class="mt-1 text-slate-500">
            Lihat tagihan pemeriksaan dan unggah bukti pembayaran Anda.
        </p>
    </div>

    <div class="overflow-x-auto rounded-2xl border border-slate-200 bg-white shadow-sm">
        <table class="table w-full">
            <thead class="bg-slate-50 text-slate-600">
                <tr>
                    <th>Tagihan</th>
                    <th>Tanggal Periksa</th>
                    <th>Poli</th>
                    <th>Dokter</th>
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
                            {{ $pembayaran->periksa?->tgl_periksa?->format('d-m-Y H:i') ?? '-' }}
                        </td>

                        <td>
                            {{ $pembayaran->periksa?->daftarPoli?->jadwalPeriksa?->dokter?->poli?->nama_poli ?? '-' }}
                        </td>

                        <td>
                            {{ $pembayaran->periksa?->daftarPoli?->jadwalPeriksa?->dokter?->nama ?? '-' }}
                        </td>

                        <td class="whitespace-nowrap font-semibold">
                            Rp {{ number_format($pembayaran->jumlah, 0, ',', '.') }}
                        </td>

                        <td>
                            <span class="inline-flex rounded-lg bg-indigo-50 px-3 py-1 text-indigo-700">
                                {{ $pembayaran->labelStatus() }}
                            </span>
                        </td>

                        <td>
                            <a href="{{ route('pasien.pembayaran.show', ['id' => $pembayaran->id]) }}"
                                class="inline-flex rounded-lg bg-indigo-600 px-4 py-2 font-semibold text-white hover:bg-indigo-700">
                                Detail
                            </a>
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