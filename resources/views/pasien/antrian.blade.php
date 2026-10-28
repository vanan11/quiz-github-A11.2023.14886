<div class="flex flex-wrap items-center justify-between gap-4 mb-6">
    <div>
        <h1 class="text-2xl font-bold text-slate-800">Dashboard Pasien</h1>
        <p class="text-slate-500 mt-1">
            Selamat datang, {{ auth()->user()->nama }}.
        </p>
    </div>

    @if (!$pendaftaranAktif)
        <a href="{{ route('pasien.daftar') }}"
            class="rounded-xl bg-indigo-600 px-5 py-3 font-semibold text-white hover:bg-indigo-700">
            Daftar Poli
        </a>
    @endif
</div>

@if ($pendaftaranAktif)
    <section class="rounded-2xl border border-indigo-200 bg-indigo-50 p-6 mb-6"
        aria-labelledby="judul-pendaftaran">
        <h2 id="judul-pendaftaran" class="text-lg font-bold text-indigo-900">
            Pendaftaran Aktif
        </h2>

        <dl class="grid grid-cols-1 gap-5 mt-5 sm:grid-cols-2 lg:grid-cols-3">
            <div>
                <dt class="text-sm text-slate-500">Poli</dt>
                <dd class="font-semibold text-slate-800">
                    {{ $pendaftaranAktif->jadwalPeriksa?->dokter?->poli?->nama_poli ?? '-' }}
                </dd>
            </div>

            <div>
                <dt class="text-sm text-slate-500">Dokter</dt>
                <dd class="font-semibold text-slate-800">
                    {{ $pendaftaranAktif->jadwalPeriksa?->dokter?->nama ?? '-' }}
                </dd>
            </div>

            <div>
                <dt class="text-sm text-slate-500">Jadwal</dt>
                <dd class="font-semibold text-slate-800">
                    {{ $pendaftaranAktif->jadwalPeriksa?->hari ?? '-' }}
                    {{ substr($pendaftaranAktif->jadwalPeriksa?->jam_mulai ?? '', 0, 5) }}
                    – {{ substr($pendaftaranAktif->jadwalPeriksa?->jam_selesai ?? '', 0, 5) }}
                </dd>
            </div>

            <div>
                <dt class="text-sm text-slate-500">Tanggal Kunjungan</dt>
                <dd class="font-semibold text-slate-800">
                    {{ $pendaftaranAktif->tanggal_kunjungan?->format('d-m-Y') ?? 'Belum tercatat' }}
                </dd>
            </div>

            <div>
                <dt class="text-sm text-slate-500">Nomor Antrian Anda</dt>
                <dd class="text-3xl font-bold text-indigo-700">
                    {{ $pendaftaranAktif->no_antrian }}
                </dd>
            </div>

            <div>
                <dt class="text-sm text-slate-500">Sedang Dilayani</dt>
                <dd class="text-xl font-bold text-indigo-700">
                    {{ $nomorDipanggil ?? 'Belum ada panggilan' }}
                </dd>
            </div>
        </dl>

        <p class="mt-5 text-sm text-indigo-900">
            Anda dapat mendaftar kembali setelah pemeriksaan selesai.
        </p>
    </section>
@else
    <div class="rounded-xl border border-slate-200 bg-white p-5 mb-6 text-slate-600">
        Belum ada pendaftaran aktif. Silakan pilih jadwal dan daftar ke poli.
    </div>
@endif