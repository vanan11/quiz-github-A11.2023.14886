<x-layouts.app title="Pendaftaran Poli">

    <h2 class="text-2xl font-bold text-slate-800 mb-6">
        Pendaftaran Poli
    </h2>

    @if (session('message'))
        <div class="alert alert-success mb-6">
            {{ session('message') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="p-4 mb-6 rounded-lg bg-red-50 text-red-700">
            <ul class="list-disc pl-5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card bg-base-100 shadow-md border border-slate-200 rounded-2xl">
        <div class="card-body p-8">

            <form action="{{ route('pasien.daftar.submit') }}"
                method="POST"
                class="space-y-5">
                @csrf

                <input type="hidden"
                    name="id_pasien"
                    value="{{ $user->id }}">

                <div>
                    <label class="block font-semibold mb-2">
                        Nama Pasien
                    </label>

                    <input type="text"
                    value="{{ $user->nama }}"
                    class="w-full border rounded-lg p-3 bg-slate-100"
                    readonly>
                </div>

                <div>
                    <label for="id_poli" class="block font-semibold mb-2">
                        Pilih Poli
                    </label>

                    <select id="id_poli"
                        name="id_poli"
                        class="w-full border rounded-lg p-3"
                        required>
                        <option value="">-- Pilih Poli --</option>

                        @foreach ($polis as $poli)
                            <option value="{{ $poli->id }}"
                                @selected(old('id_poli') == $poli->id)>
                                {{ $poli->nama_poli }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="id_jadwal" class="block font-semibold mb-2">
                        Pilih Jadwal Periksa
                    </label>

                    <select id="id_jadwal"
                        name="id_jadwal"
                        class="w-full border rounded-lg p-3"
                        required>
                        <option value="">-- Pilih Jadwal --</option>

                        @foreach ($jadwals as $jadwal)
                            <option value="{{ $jadwal->id }}"
                                data-poli="{{ $jadwal->dokter?->poli?->id }}"
                                @selected(old('id_jadwal') == $jadwal->id)>
                                {{ $jadwal->dokter?->nama ?? 'Dokter' }}
                                — {{ $jadwal->hari }}
                                ({{ substr($jadwal->jam_mulai, 0, 5) }}
                                - {{ substr($jadwal->jam_selesai, 0, 5) }})
                            </option>
                        @endforeach
                    </select>

                    <p class="text-sm text-slate-500 mt-2">
                        Pilih poli terlebih dahulu untuk melihat jadwalnya.
                    </p>
                </div>

                <div>
                    <label for="keluhan" class="block font-semibold mb-2">
                        Keluhan
                    </label>

                    <textarea id="keluhan"
                        name="keluhan"
                        rows="4"
                        class="w-full border rounded-lg p-3"
                        placeholder="Tuliskan keluhan..."
                    >{{ old('keluhan') }}</textarea>
                </div>

                <button type="submit"
                    class="bg-primary hover:bg-primary/90 text-white
                           px-6 py-3 rounded-lg font-semibold">
                    Daftar Poli
                </button>
            </form>

        </div>
    </div>

    <script>
        (() => {
            const poli = document.getElementById('id_poli');
            const jadwal = document.getElementById('id_jadwal');

            function filterJadwal() {
                jadwal.disabled = poli.value === '';

                Array.from(jadwal.options).forEach(option => {
                    if (option.value === '') return;

                    const sesuai = poli.value !== ''
                        && option.dataset.poli === poli.value;

                    option.hidden = !sesuai;
                    option.disabled = !sesuai;
                });

                if (jadwal.selectedOptions[0]?.disabled) {
                    jadwal.value = '';
                }
            }

            poli.addEventListener('change', () => {
                jadwal.value = '';
                filterJadwal();
            });

            filterJadwal();
        })();
    </script>

</x-layouts.app>