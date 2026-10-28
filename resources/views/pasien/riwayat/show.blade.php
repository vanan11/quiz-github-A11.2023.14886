<x-layouts.app title="Detail Riwayat Pemeriksaan">
    <div class="mb-6 flex items-center gap-3">
        <a href="{{ route('pasien.riwayat.index') }}"
            class="rounded-lg bg-white px-4 py-2 text-slate-600 hover:bg-slate-100">
            Kembali
        </a>

        <h1 class="text-2xl font-bold text-slate-800">
            Detail Riwayat Pemeriksaan
        </h1>
    </div>

    @php
        $selesai = $pendaftaran->periksas->isNotEmpty()
            || $pendaftaran->status === \App\Models\DaftarPoli::STATUS_SELESAI;
    @endphp

    <div class="flex flex-col gap-5">
        <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="mb-4 text-lg font-bold text-slate-800">
                Informasi Pendaftaran
            </h2>

            <dl class="grid grid-cols-1 gap-4 text-sm sm:grid-cols-2">
                <div>
                    <dt class="text-slate-500">Poli</dt>
                    <dd class="font-semibold text-slate-800">
                        {{ $pendaftaran->jadwalPeriksa?->dokter?->poli?->nama_poli ?? '-' }}
                    </dd>
                </div>

                <div>
                    <dt class="text-slate-500">Dokter</dt>
                    <dd class="font-semibold text-slate-800">
                        {{ $pendaftaran->jadwalPeriksa?->dokter?->nama ?? '-' }}
                    </dd>
                </div>

                <div>
                    <dt class="text-slate-500">Tanggal Kunjungan</dt>
                    <dd class="font-semibold text-slate-800">
                        {{ $pendaftaran->tanggal_kunjungan?->format('d-m-Y') ?? 'Belum tercatat' }}
                    </dd>
                </div>

                <div>
                    <dt class="text-slate-500">Nomor Antrian</dt>
                    <dd class="font-semibold text-slate-800">
                        {{ $pendaftaran->no_antrian }}
                    </dd>
                </div>

                <div>
                    <dt class="text-slate-500">Status</dt>
                    <dd class="font-semibold text-slate-800">
                        {{ $selesai ? 'Selesai' : ($pendaftaran->status === \App\Models\DaftarPoli::STATUS_DIPANGGIL ? 'Dipanggil' : 'Menunggu') }}
                    </dd>
                </div>

                <div>
                    <dt class="text-slate-500">Keluhan</dt>
                    <dd class="whitespace-pre-line text-slate-800">{{ $pendaftaran->keluhan }}</dd>
                </div>
            </dl>
        </section>

        @forelse ($pendaftaran->periksas as $periksa)
            <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="text-lg font-bold text-slate-800">
                    Hasil Pemeriksaan
                </h2>

                <p class="mt-1 text-sm text-slate-500">
                    Tanggal Periksa:
                    {{ \Carbon\Carbon::parse($periksa->tgl_periksa)->format('d-m-Y H:i') }}
                </p>

                <h3 class="mt-5 font-semibold text-slate-800">
                    Catatan Dokter
                </h3>

                <p class="mt-2 whitespace-pre-line text-sm text-slate-600">{{ $periksa->catatan ?: 'Tidak ada catatan dokter.' }}</p>

                <h3 class="mt-5 font-semibold text-slate-800">
                    Obat yang Diresepkan
                </h3>

                <div class="mt-3 overflow-x-auto">
                    <table class="table w-full">
                        <thead class="bg-slate-50 text-slate-600">
                            <tr>
                                <th>Nama Obat</th>
                                <th>Harga</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse ($periksa->detailPeriksas as $detail)
                                <tr>
                                    <td>
                                        {{ $detail->obat?->nama_obat ?? 'Obat tidak tersedia' }}
                                    </td>
                                    <td>
                                        Rp {{ number_format($detail->harga_satuan ?? $detail->obat?->harga ?? 0, 0, ',', '.') }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="2" class="text-slate-500">
                                        Tidak ada obat yang diresepkan.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-5 rounded-xl bg-indigo-50 p-4">
                    <p class="text-sm text-indigo-900">
                        Total Biaya Pemeriksaan
                    </p>

                    <p class="mt-1 text-2xl font-bold text-indigo-700">
                        Rp {{ number_format($periksa->biaya_periksa, 0, ',', '.') }}
                    </p>

                    <p class="mt-1 text-sm text-slate-500">
                        Termasuk biaya jasa dokter dan obat.
                    </p>
                </div>
            </section>
        @empty
            <div class="rounded-xl border border-slate-200 bg-white p-5 text-slate-600">
                Hasil pemeriksaan belum tersedia.
            </div>
        @endforelse
    </div>
</x-layouts.app>