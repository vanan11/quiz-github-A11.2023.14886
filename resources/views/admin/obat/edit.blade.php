
<x-layouts.app title="Edit Obat">

    {{-- HEADER --}}
    <div class="flex items-center gap-3 mb-6">

        <a href="{{ route('obat.index') }}"
            class="flex items-center justify-center w-9 h-9
                   rounded-lg bg-slate-100 hover:bg-slate-200
                   text-slate-600 transition">

            <i class="fas fa-arrow-left text-sm"></i>
        </a>

        <h2 class="text-2xl font-bold text-slate-800">
            Edit Obat
        </h2>

    </div>

    {{-- CARD FORM --}}
    <div class="card bg-base-100 shadow-md rounded-2xl
                border border-slate-200">

        <div class="card-body p-8">

            <form action="{{ route('obat.update', $obat->id) }}"
                  method="POST">

                @csrf
                @method('PUT')

                {{-- NAMA OBAT DAN KEMASAN --}}
                <div class="grid grid-cols-1 md:grid-cols-2
                            gap-6 mb-6">

                    {{-- Nama Obat --}}
                    <div>

                        <label for="nama_obat"
                            class="block text-sm font-semibold
                                   text-slate-700 mb-1">

                            Nama Obat
                            <span class="text-red-500">*</span>

                        </label>

                        <input
                            type="text"
                            id="nama_obat"
                            name="nama_obat"
                            value="{{ old('nama_obat', $obat->nama_obat) }}"
                            maxlength="255"
                            placeholder="Masukkan nama obat..."
                            class="w-full px-4 py-2 border-2
                                   rounded-lg focus:border-primary
                                   focus:outline-none
                                   @error('nama_obat') border-red-500 @enderror"
                            required>

                        @error('nama_obat')
                            <p class="text-xs text-red-500 mt-1">
                                {{ $message }}
                            </p>
                        @enderror

                    </div>

                    {{-- Kemasan --}}
                    <div>

                        <label for="kemasan"
                            class="block text-sm font-semibold
                                   text-slate-700 mb-1">

                            Kemasan

                        </label>

                        <input
                            type="text"
                            id="kemasan"
                            name="kemasan"
                            value="{{ old('kemasan', $obat->kemasan) }}"
                            maxlength="35"
                            placeholder="Contoh: Strip, Botol, Tube..."
                            class="w-full px-4 py-2 border-2
                                   rounded-lg focus:border-primary
                                   focus:outline-none
                                   @error('kemasan') border-red-500 @enderror">

                        @error('kemasan')
                            <p class="text-xs text-red-500 mt-1">
                                {{ $message }}
                            </p>
                        @enderror

                    </div>

                </div>

                {{-- HARGA DAN STOK --}}
                <div class="grid grid-cols-1 md:grid-cols-2
                            gap-6 mb-8">

                    {{-- Harga Obat --}}
                    <div>

                        <label for="harga"
                            class="block text-sm font-semibold
                                   text-slate-700 mb-1">

                            Harga Obat
                            <span class="text-red-500">*</span>

                        </label>

                        <div class="flex items-center border-2
                                    rounded-lg px-4 py-2
                                    focus-within:border-primary
                                    @error('harga') border-red-500 @enderror">

                            <span class="text-slate-500 text-sm
                                         font-semibold mr-2">
                                Rp
                            </span>

                            <input
                                type="number"
                                id="harga"
                                name="harga"
                                value="{{ old('harga', $obat->harga) }}"
                                min="0"
                                max="2147483647"
                                step="1"
                                placeholder="0"
                                class="w-full bg-transparent
                                       focus:outline-none"
                                required>

                        </div>

                        @error('harga')
                            <p class="text-xs text-red-500 mt-1">
                                {{ $message }}
                            </p>
                        @enderror

                        <p class="text-xs text-slate-400 mt-1">
                            Masukkan harga dalam Rupiah.
                        </p>

                    </div>

                    {{-- Stok Obat --}}
                    <div>

                        <label for="stok"
                            class="block text-sm font-semibold
                                   text-slate-700 mb-1">

                            Stok Obat
                            <span class="text-red-500">*</span>

                        </label>

                        <div class="flex items-center border-2
                                    rounded-lg px-4 py-2
                                    focus-within:border-primary
                                    @error('stok') border-red-500 @enderror">

                            <input
                                type="number"
                                id="stok"
                                name="stok"
                                value="{{ old('stok', $obat->stok ?? 0) }}"
                                min="0"
                                max="4294967295"
                                step="1"
                                placeholder="0"
                                class="w-full bg-transparent
                                       focus:outline-none"
                                required>

                            <span class="text-slate-400 text-xs
                                         font-medium ml-2">
                                Unit
                            </span>

                        </div>

                        @error('stok')
                            <p class="text-xs text-red-500 mt-1">
                                {{ $message }}
                            </p>
                        @enderror

                        <p class="text-xs text-slate-400 mt-1">
                            Ubah jumlah persediaan obat yang tersedia.
                        </p>

                    </div>

                </div>

                {{-- TOMBOL AKSI --}}
                <div class="flex flex-wrap gap-3">

                    <button
                        type="submit"
                        class="px-6 py-2.5 rounded-xl bg-primary
                               hover:bg-primary/90 text-white
                               font-semibold text-sm transition">

                        <i class="fas fa-save mr-1"></i>
                        Simpan Perubahan

                    </button>

                    <a href="{{ route('obat.index') }}"
                        class="px-6 py-2.5 rounded-xl
                               bg-slate-100 hover:bg-slate-200
                               text-slate-600 font-semibold
                               text-sm transition">

                        Batal

                    </a>

                </div>

            </form>

        </div>

    </div>

</x-layouts.app>
