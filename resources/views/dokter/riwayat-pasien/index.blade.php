
<x-layouts.app title="Riwayat Pasien">

    {{-- ========================================= --}}
    {{-- HEADER --}}
    {{-- ========================================= --}}

    <div class="flex flex-wrap items-center justify-between gap-4 mb-6">

        <h2 class="text-2xl font-bold text-slate-800">
            Riwayat Pasien
        </h2>

        {{-- Tombol Export Excel --}}
        @if (Route::has('riwayat-pasien.export'))

        <a href="{{ route('riwayat-pasien.export') }}"
            class="btn bg-emerald-600 hover:bg-emerald-700
                   text-white border-none rounded-lg px-5
                   inline-flex items-center gap-2 transition">

            <i class="fas fa-file-excel"></i>
            Export Excel

        </a>

        @endif

    </div>

    {{-- ========================================= --}}
    {{-- CARD TABLE --}}
    {{-- ========================================= --}}

    <div class="card bg-base-100 shadow-md rounded-2 border">

        <div class="card-body p-0">

            <div class="overflow-x-auto">

                <table class="table table-zebra w-full">

                    {{-- ================================= --}}
                    {{-- TABLE HEAD --}}
                    {{-- ================================= --}}

                    <thead class="bg-slate-100 text-slate-500
                                  text-xs uppercase tracking-wider">

                        <tr>

                            <th class="px-6 py-4">
                                No Antrian
                            </th>

                            <th class="px-6 py-4">
                                Nama Pasien
                            </th>

                            <th class="px-6 py-4">
                                Keluhan
                            </th>

                            <th class="px-6 py-4">
                                Tanggal Periksa
                            </th>

                            <th class="px-6 py-4">
                                Biaya
                            </th>

                            <th class="px-6 py-4 text-right">
                                Aksi
                            </th>

                        </tr>

                    </thead>

                    {{-- ================================= --}}
                    {{-- TABLE BODY --}}
                    {{-- ================================= --}}

                    <tbody>

                        @forelse($riwayatPasien as $riwayat)

                        <tr class="hover:bg-slate-50 transition">

                            {{-- Nomor Antrian --}}
                            <td class="px-6 py-4 text-slate-500">

                                {{ $riwayat->daftarPoli->no_antrian }}

                            </td>

                            {{-- Nama Pasien --}}
                            <td class="px-6 py-4 font-semibold text-slate-800">

                                {{ $riwayat->daftarPoli->pasien->nama }}

                            </td>

                            {{-- Keluhan --}}
                            <td class="px-6 py-4 text-slate-500">

                                {{ $riwayat->daftarPoli->keluhan }}

                            </td>

                            {{-- Tanggal Periksa --}}
                            <td class="px-6 py-4 text-slate-500">

                                {{
                                    \Carbon\Carbon::parse(
                                        $riwayat->tgl_periksa
                                    )->format('d/m/Y')
                                }}

                            </td>

                            {{-- Biaya Pemeriksaan --}}
                            <td class="px-6 py-4 text-slate-500">

                                Rp {{
                                    number_format(
                                        $riwayat->biaya_periksa,
                                        0,
                                        ',',
                                        '.'
                                    )
                                }}

                            </td>

                            {{-- ================================= --}}
                            {{-- AKSI --}}
                            {{-- ================================= --}}

                            <td class="px-6 py-4 text-right">

                                {{-- Detail Riwayat --}}
                                <a href="{{ route('riwayat-pasien.show', $riwayat) }}"
                                    class="btn btn-sm bg-blue-500
                                           hover:bg-blue-600
                                           text-white border-none
                                           rounded-lg px-4
                                           inline-flex items-center gap-2">

                                    <i class="fas fa-eye"></i>
                                    Detail

                                </a>

                            </td>

                        </tr>

                        @empty

                        {{-- ================================= --}}
                        {{-- DATA KOSONG --}}
                        {{-- ================================= --}}

                        <tr>

                            <td colspan="6"
                                class="text-center py-14 text-slate-400">

                                <div class="flex flex-col items-center
                                            justify-center gap-2">

                                    <i class="fas fa-inbox text-3xl"></i>

                                    <span>
                                        Belum ada riwayat pemeriksaan
                                    </span>

                                </div>

                            </td>

                        </tr>

                        @endforelse

                    </tbody>

                </table>

            </div>

        </div>

    </div>

</x-layouts.app>
