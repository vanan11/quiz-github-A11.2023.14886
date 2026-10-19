<x-layouts.app title="Periksa Pasien">

    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('periksa-pasien.index') }}"
            class="flex items-center justify-center w-9 h-9 rounded-lg
                   bg-slate-100 hover:bg-slate-200 text-slate-600">
            <i class="fas fa-arrow-left"></i>
        </a>

        <h2 class="text-2xl font-bold text-slate-800">
            Periksa Pasien
        </h2>
    </div>

    @if ($errors->any())
        <div class="p-4 mb-6 bg-red-50 text-red-700 border
                    border-red-200 rounded-lg">
            <ul class="list-disc pl-5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card bg-base-100 shadow-md rounded-2xl border border-slate-200">
        <div class="card-body p-8">

            <form id="form-periksa"
                action="{{ route('periksa-pasien.store') }}"
                method="POST">
                @csrf

                <input type="hidden" name="obat_json" id="obat_json">
                <input type="hidden" name="biaya_periksa" id="biaya_periksa">

                <input type="hidden"
                name="id_daftar_poli"
                value="{{ $id }}">

                {{-- Pilih obat --}}
                <div class="mb-6">
                    <label for="pilih-obat"
                        class="block text-sm font-semibold text-slate-700 mb-2">
                        Pilih Obat <span class="text-red-500">*</span>
                    </label>

                    <select id="pilih-obat"
                        class="w-full px-4 py-2 border-2 rounded-lg
                               focus:border-primary focus:outline-none">
                        <option value="">-- Pilih Obat --</option>

                        @foreach ($obats as $obat)
                            <option value="{{ $obat->id }}"
                                data-nama="{{ $obat->nama_obat }}"
                                data-harga="{{ $obat->harga }}">
                                {{ $obat->nama_obat }}
                                — Rp{{ number_format($obat->harga, 0, ',', '.') }}
                            </option>
                        @endforeach
                    </select>

                    <p id="error-obat"
                        class="text-sm text-red-500 mt-2"
                        role="alert"></p>
                </div>

                {{-- Obat terpilih --}}
                <div class="mb-6">
                    <label class="block text-sm font-semibold text-slate-700 mb-2">
                        Obat Terpilih
                    </label>

                    <div id="obat-terpilih" class="space-y-2"></div>
                </div>

                {{-- Total harga --}}
                <div class="mb-6">
                    <label for="total-harga-tampil"
                        class="block text-sm font-semibold text-slate-700 mb-2">
                        Total Harga
                    </label>

                    <input type="text"
                        id="total-harga-tampil"
                        value="Rp 0"
                        class="w-full px-4 py-2 rounded-lg
                               bg-slate-100 text-slate-700 font-semibold"
                        readonly>

                    <input type="hidden"
                        id="total-harga"
                        name="total_harga"
                        value="0">
                </div>

                {{-- Catatan --}}
                <div class="mb-8">
                    <label for="catatan"
                        class="block text-sm font-semibold text-slate-700 mb-2">
                        Catatan
                        <span class="font-normal text-slate-400">(opsional)</span>
                    </label>

                    <textarea id="catatan"
                        name="catatan"
                        rows="4"
                        placeholder="Masukkan catatan pemeriksaan..."
                        class="w-full px-4 py-2 border-2 rounded-lg
                               focus:border-primary focus:outline-none"
                    >{{ old('catatan') }}</textarea>
                </div>

                <div class="flex gap-3">
                    <button type="submit"
                        class="px-6 py-2.5 rounded-xl bg-primary
                               hover:bg-primary/90 text-white
                               font-semibold text-sm transition">
                        <i class="fas fa-save mr-1"></i>
                        Simpan
                    </button>

                    <a href="{{ route('periksa-pasien.index') }}"
                        class="px-6 py-2.5 rounded-xl bg-slate-100
                               hover:bg-slate-200 text-slate-600
                               font-semibold text-sm transition">
                        Batal
                    </a>
                </div>

            </form>

            <script>
                    (() => {
                        const obatJson = document.getElementById('obat_json');
                        const biayaPeriksa = document.getElementById('biaya_periksa');
                        const form = obatJson.closest('form');

                        form.addEventListener('submit', () => {
                            const obatIds = Array.from(
                                form.querySelectorAll('input[name="obat[]"]'),
                                input => Number(input.value)
                            );

                            const totalHarga = form.querySelector(
                                'input[name="total_harga"]'
                            );

                            obatJson.value = JSON.stringify(obatIds);
                            biayaPeriksa.value = totalHarga.value;
                        });
                    })();
                </script>

        </div>
    </div>

    <script>
        (() => {
            const form = document.getElementById('form-periksa');
            const select = document.getElementById('pilih-obat');
            const daftar = document.getElementById('obat-terpilih');
            const totalTampil = document.getElementById('total-harga-tampil');
            const totalInput = document.getElementById('total-harga');
            const errorObat = document.getElementById('error-obat');

            const rupiah = new Intl.NumberFormat('id-ID', {
                style: 'currency',
                currency: 'IDR',
                maximumFractionDigits: 0
            });

            const options = Array.from(select.options)
                .filter(option => option.value !== '');

            const katalog = new Map(options.map(option => [
                option.value,
                {
                    id: option.value,
                    nama: option.dataset.nama,
                    harga: Number(option.dataset.harga)
                }
            ]));

            const pilihan = new Map();

            // Kembalikan pilihan setelah validasi server gagal.
            const obatLama = @json(old('obat', []));

            if (Array.isArray(obatLama)) {
                obatLama.forEach(id => {
                    const obat = katalog.get(String(id));

                    if (obat) {
                        pilihan.set(obat.id, obat);
                    }
                });
            }

            function renderObat() {
                daftar.replaceChildren();

                let total = 0;

                if (pilihan.size === 0) {
                    const kosong = document.createElement('p');
                    kosong.className = 'text-sm text-slate-400';
                    kosong.textContent = 'Belum ada obat dipilih.';
                    daftar.appendChild(kosong);
                }

                pilihan.forEach(obat => {
                    total += obat.harga;

                    const baris = document.createElement('div');
                    baris.className =
                        'flex items-center justify-between gap-3 ' +
                        'p-3 bg-slate-50 border border-slate-200 rounded-lg';

                    const nama = document.createElement('span');
                    nama.className = 'text-sm text-slate-700';
                    nama.textContent =
                        obat.nama + ' - ' + rupiah.format(obat.harga);

                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'obat[]';
                    input.value = obat.id;

                    const hapus = document.createElement('button');
                    hapus.type = 'button';
                    hapus.className =
                        'text-sm font-semibold text-red-500 hover:text-red-700';
                    hapus.textContent = 'Hapus';
                    hapus.setAttribute('aria-label', 'Hapus ' + obat.nama);

                    hapus.addEventListener('click', () => {
                        pilihan.delete(obat.id);
                        renderObat();
                    });

                    baris.append(nama, input, hapus);
                    daftar.appendChild(baris);
                });

                totalTampil.value = rupiah.format(total);
                totalInput.value = total;

                // Obat yang sama hanya dapat dipilih sekali.
                options.forEach(option => {
                    option.disabled = pilihan.has(option.value);
                });
            }

            select.addEventListener('change', () => {
                const obat = katalog.get(select.value);

                if (!obat) return;

                pilihan.set(obat.id, obat);
                select.value = '';
                errorObat.textContent = '';

                renderObat();
            });

            form.addEventListener('submit', event => {
                if (pilihan.size === 0) {
                    event.preventDefault();
                    errorObat.textContent =
                        'Pilih minimal satu obat terlebih dahulu.';
                    select.focus();
                }
            });

            renderObat();
        })();
    </script>

</x-layouts.app>