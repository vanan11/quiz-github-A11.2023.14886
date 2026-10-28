<div class="rounded-2xl border border-slate-200 bg-white shadow-sm">
    <div class="overflow-x-auto">
        <table class="table w-full">
            <thead class="bg-slate-50 text-slate-600">
                <tr>
                    <th>ID</th>
                    <th>Nama Pasien</th>
                    <th>Kunjungan</th>
                    <th>Keluhan</th>
                    <th>Antrian</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>

            <tbody>
                @forelse ($daftarPasien as $daftarPoli)
                    @php
                        $selesai = $daftarPoli->periksas->isNotEmpty()
                            || $daftarPoli->status === \App\Models\DaftarPoli::STATUS_SELESAI;

                        $dipanggil = $daftarPoli->status === \App\Models\DaftarPoli::STATUS_DIPANGGIL;
                    @endphp

                    <tr>
                        <td>{{ $daftarPoli->id }}</td>
                        <td class="font-semibold">
                            {{ $daftarPoli->pasien?->nama ?? '-' }}
                        </td>
                        <td>
                            <div>
                                {{ $daftarPoli->tanggal_kunjungan?->format('d-m-Y') ?? 'Belum tercatat' }}
                            </div>
                            <div class="text-sm text-slate-500">
                                {{ $daftarPoli->jadwalPeriksa?->hari ?? '-' }}
                                {{ substr($daftarPoli->jadwalPeriksa?->jam_mulai ?? '', 0, 5) }}
                                – {{ substr($daftarPoli->jadwalPeriksa?->jam_selesai ?? '', 0, 5) }}
                            </div>
                        </td>
                        <td>{{ $daftarPoli->keluhan }}</td>
                        <td class="font-bold">{{ $daftarPoli->no_antrian }}</td>
                        <td>
                            @if ($selesai)
                                <span class="rounded-lg bg-emerald-100 px-3 py-1 text-emerald-700">
                                    Selesai
                                </span>
                            @elseif ($dipanggil)
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
                            @if ($selesai)
                                <span class="text-slate-400">Sudah diperiksa</span>
                            @elseif ($dipanggil)
                                <a href="{{ route('periksa-pasien.create', ['id' => $daftarPoli->id]) }}"
                                    class="inline-block rounded-lg bg-amber-500 px-4 py-2 font-semibold text-white hover:bg-amber-600">
                                    Periksa
                                </a>
                            @else
                                <form method="POST"
                                    action="{{ route('periksa-pasien.panggil', ['id' => $daftarPoli->id]) }}">
                                    @csrf
                                    <button type="submit"
                                        class="rounded-lg bg-indigo-600 px-4 py-2 font-semibold text-white hover:bg-indigo-700">
                                        Panggil
                                    </button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="py-8 text-center text-slate-500">
                            Belum ada pasien yang mendaftar.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>