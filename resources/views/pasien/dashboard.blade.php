<x-layouts.app title="Dashboard Pasien">
    <div data-antrian-live
        data-snapshot-url="{{ route('pasien.dashboard') }}"
        data-channels="{{ json_encode($channels) }}">
        <div data-antrian-content>
            @include('pasien.antrian', [
                'pendaftaranAktif' => $pendaftaranAktif,
                'nomorDipanggil' => $nomorDipanggil,
            ])
        </div>

        <p data-antrian-status role="status" aria-live="polite"
            class="mb-6 text-sm text-slate-500">
            Menghubungkan pembaruan antrean...
        </p>
    </div>

    <div class="rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="border-b border-slate-200 p-5">
            <h2 class="text-lg font-bold text-slate-800">Jadwal Dokter</h2>
        </div>

        <div class="overflow-x-auto">
            <table class="table w-full">
                <thead class="bg-slate-50 text-slate-600">
                    <tr>
                        <th>Poli</th>
                        <th>Dokter</th>
                        <th>Hari</th>
                        <th>Jam Periksa</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($jadwals as $jadwal)
                        <tr>
                            <td>{{ $jadwal->dokter?->poli?->nama_poli ?? '-' }}</td>
                            <td>{{ $jadwal->dokter?->nama ?? '-' }}</td>
                            <td>{{ $jadwal->hari }}</td>
                            <td>
                                {{ substr($jadwal->jam_mulai, 0, 5) }}
                                – {{ substr($jadwal->jam_selesai, 0, 5) }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="py-8 text-center text-slate-500">
                                Belum ada jadwal dokter.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-layouts.app>