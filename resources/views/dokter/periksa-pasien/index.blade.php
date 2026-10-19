<x-layouts.app title="Periksa Pasien">

    <h2 class="text-2xl font-bold text-slate-800 mb-6">
        Periksa Pasien
    </h2>

    {{-- Notifikasi --}}
    @if (session('message'))
        <div class="alert alert-success mb-6">
            <i class="fas fa-check-circle"></i>
            <span>{{ session('message') }}</span>
        </div>
    @endif

    {{-- Tabel daftar pasien --}}
    <div class="card bg-base-100 shadow-md rounded-2xl border border-slate-200">
        <div class="card-body p-6">

            <div class="overflow-x-auto">
                <table class="table w-full">
                    <thead class="bg-slate-50 text-slate-600">
                        <tr>
                            <th>ID</th>
                            <th>Nama Pasien</th>
                            <th>Keluhan</th>
                            <th>Nomor Antrian</th>
                            <th class="text-right">Aksi</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($daftarPasien as $daftarPoli)
                            <tr class="hover:bg-slate-50">
                                <td>{{ $daftarPoli->id }}</td>

                                <td class="font-semibold">
                                    {{ $daftarPoli->pasien?->nama ?? '-' }}
                                </td>

                                <td>{{ $daftarPoli->keluhan }}</td>

                                <td>{{ $daftarPoli->no_antrian }}</td>

                                <td class="text-right">
                                    @if ($daftarPoli->periksas->isNotEmpty())
                                        <span class="inline-flex items-center gap-1
                                                     px-3 py-1.5 rounded-lg
                                                     bg-emerald-100 text-emerald-700
                                                     text-xs font-semibold">
                                            <i class="fas fa-check-circle"></i>
                                            Sudah Diperiksa
                                        </span>
                                    @else
                                        <a
                                           href="{{ route('periksa-pasien.create', ['id' => $daftarPoli->id]) }}"
                                            class="inline-flex items-center gap-1
                                                   px-3 py-1.5 rounded-lg
                                                   bg-amber-500 hover:bg-amber-600
                                                   text-white text-xs font-semibold
                                                   transition"
                                        >
                                            <i class="fas fa-stethoscope"></i>
                                            Periksa
                                        </a>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5"
                                    class="text-center py-8 text-slate-500">
                                    Belum ada pasien yang mendaftar.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

        </div>
    </div>

</x-layouts.app>