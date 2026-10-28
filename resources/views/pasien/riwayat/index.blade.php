<x-layouts.app title="Riwayat Pemeriksaan">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-slate-800">
            Riwayat Pemeriksaan
        </h1>
        <p class="mt-1 text-slate-500">
            Daftar pendaftaran dan hasil pemeriksaan Anda.
        </p>
    </div>

    <div class="rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="table w-full">
                <thead class="bg-slate-50 text-slate-600">
                    <tr>
                        <th>Tanggal Kunjungan</th>
                        <th>Poli</th>
                        <th>Dokter</th>
                        <th>Antrian</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($riwayats as $riwayat)
                        @php
                            $selesai = $riwayat->periksas_count > 0
                                || $riwayat->status === \App\Models\DaftarPoli::STATUS_SELESAI;
                        @endphp

                        <tr>
                            <td>
                                {{ $riwayat->tanggal_kunjungan?->format('d-m-Y') ?? 'Belum tercatat' }}
                            </td>

                            <td>
                                {{ $riwayat->jadwalPeriksa?->dokter?->poli?->nama_poli ?? '-' }}
                            </td>

                            <td>
                                {{ $riwayat->jadwalPeriksa?->dokter?->nama ?? '-' }}
                            </td>

                            <td class="font-semibold">
                                {{ $riwayat->no_antrian }}
                            </td>

                            <td>
                                @if ($selesai)
                                    <span class="rounded-lg bg-emerald-100 px-3 py-1 text-emerald-700">
                                        Selesai
                                    </span>
                                @elseif ($riwayat->status === \App\Models\DaftarPoli::STATUS_DIPANGGIL)
                                    <span class="rounded-lg bg-indigo-100 px-3 py-1 text-indigo-700">
                                        Dipanggil
                                    </span>
                                @else
                                    <span class="rounded-lg bg-amber-100 px-3 py-1 text-amber-700">
                                        Menunggu
                                    </span>
                                @endif
                            </td>

                            <td>
                                <a href="{{ route('pasien.riwayat.show', ['id' => $riwayat->id]) }}"
                                    class="inline-flex rounded-lg bg-indigo-600 px-4 py-2 font-semibold text-white hover:bg-indigo-700">
                                    Detail
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-8 text-center text-slate-500">
                                Belum ada riwayat pendaftaran.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-5">
        {{ $riwayats->links() }}
    </div>
</x-layouts.app>