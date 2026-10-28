<x-layouts.app title="Detail Pembayaran">
    <div class="mb-6 flex items-center gap-3">
        <a href="{{ route('pasien.pembayaran.index') }}"
            class="rounded-lg bg-white px-4 py-2 text-slate-600 hover:bg-slate-100">
            Kembali
        </a>

        <h1 class="text-2xl font-bold text-slate-800">
            Pembayaran #{{ $pembayaran->id }}
        </h1>
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

    <div class="flex flex-col gap-5">
        <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="mb-4 text-lg font-bold text-slate-800">
                Informasi Tagihan
            </h2>

            <dl class="grid grid-cols-1 gap-4 text-sm sm:grid-cols-2">
                <div>
                    <dt class="text-slate-500">Poli</dt>
                    <dd class="font-semibold text-slate-800">
                        {{ $pembayaran->periksa?->daftarPoli?->jadwalPeriksa?->dokter?->poli?->nama_poli ?? '-' }}
                    </dd>
                </div>

                <div>
                    <dt class="text-slate-500">Dokter</dt>
                    <dd class="font-semibold text-slate-800">
                        {{ $pembayaran->periksa?->daftarPoli?->jadwalPeriksa?->dokter?->nama ?? '-' }}
                    </dd>
                </div>

                <div>
                    <dt class="text-slate-500">Tanggal Periksa</dt>
                    <dd class="font-semibold text-slate-800">
                        {{ $pembayaran->periksa?->tgl_periksa?->format('d-m-Y H:i') ?? '-' }}
                    </dd>
                </div>

                <div>
                    <dt class="text-slate-500">Status Pembayaran</dt>
                    <dd class="font-semibold text-indigo-700">
                        {{ $pembayaran->labelStatus() }}
                    </dd>
                </div>
            </dl>

            <div class="mt-5 rounded-xl bg-indigo-50 p-4">
                <p class="text-sm text-indigo-900">Total Tagihan</p>

                <p class="mt-1 text-2xl font-bold text-indigo-700">
                    Rp {{ number_format($pembayaran->jumlah, 0, ',', '.') }}
                </p>

                <p class="mt-1 text-sm text-slate-500">
                    Termasuk biaya jasa dokter dan obat.
                </p>
            </div>

            @if ($pembayaran->periksa)
                <a href="{{ route('pasien.riwayat.show', ['id' => $pembayaran->periksa->id_daftar_poli]) }}"
                    class="mt-4 inline-flex font-semibold text-indigo-700 hover:underline">
                    Lihat hasil pemeriksaan
                </a>
            @endif
        </section>

        <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="mb-4 text-lg font-bold text-slate-800">
                Bukti Pembayaran
            </h2>

            @if ($pembayaran->bukti_path)
                <a href="{{ route('pasien.pembayaran.bukti', ['id' => $pembayaran->id]) }}"
                    target="_blank" rel="noopener">
                    <img src="{{ route('pasien.pembayaran.bukti', ['id' => $pembayaran->id]) }}"
                        alt="Bukti pembayaran tagihan {{ $pembayaran->id }}"
                        class="mb-3 max-h-96 max-w-full rounded-xl border border-slate-200 object-contain">
                </a>

                <p class="mb-4 text-sm text-slate-500">
                    Diunggah: {{ $pembayaran->diunggah_pada?->format('d-m-Y H:i') ?? '-' }}
                </p>
            @endif

            @if ($pembayaran->status === \App\Models\Pembayaran::STATUS_BELUM_BAYAR)
                <form method="POST"
                    action="{{ route('pasien.pembayaran.unggah', ['id' => $pembayaran->id]) }}"
                    enctype="multipart/form-data"
                    class="flex flex-col gap-4">
                    @csrf

                    <div>
                        <label for="bukti"
                            class="mb-2 block text-sm font-semibold text-slate-700">
                            Foto Bukti Pembayaran
                        </label>

                        <input type="file"
                            name="bukti"
                            id="bukti"
                            accept="image/jpeg,image/png,image/webp"
                            required
                            class="block w-full rounded-lg border border-slate-300 p-3 text-sm">

                        <p class="mt-2 text-sm text-slate-500">
                            Format JPG, PNG, atau WEBP. Maksimal 4 MB.
                        </p>
                    </div>

                    <button type="submit"
                        class="self-start rounded-lg bg-indigo-600 px-5 py-3 font-semibold text-white hover:bg-indigo-700">
                        Kirim Bukti Pembayaran
                    </button>
                </form>
            @elseif ($pembayaran->status === \App\Models\Pembayaran::STATUS_MENUNGGU_VERIFIKASI)
                <p class="rounded-xl bg-amber-50 p-4 text-amber-800">
                    Bukti sudah diterima. Menunggu verifikasi admin.
                </p>
            @elseif ($pembayaran->status === \App\Models\Pembayaran::STATUS_TERBAYAR)
                <p class="rounded-xl bg-emerald-50 p-4 text-emerald-800">
                    Pembayaran telah diverifikasi pada
                    {{ $pembayaran->diverifikasi_pada?->format('d-m-Y H:i') ?? '-' }}.
                </p>
            @endif
        </section>
    </div>
</x-layouts.app>