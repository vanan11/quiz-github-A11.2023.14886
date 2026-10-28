<x-layouts.app title="Periksa Pasien">
    <h2 class="text-2xl font-bold text-slate-800 mb-6">
        Periksa Pasien
    </h2>

    @if ($errors->any())
        <div class="rounded-xl border border-red-200 bg-red-50 p-4 text-red-700 mb-6"
            role="alert">
            <ul class="list-disc pl-5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div data-antrian-live
        data-snapshot-url="{{ route('periksa-pasien.index') }}"
        data-channels="{{ json_encode($channels) }}">
        <div data-antrian-content>
            @include('dokter.periksa-pasien.antrian', [
                'daftarPasien' => $daftarPasien,
            ])
        </div>

        <p data-antrian-status role="status" aria-live="polite"
            class="mb-6 mt-4 text-sm text-slate-500">
            Menghubungkan pembaruan antrean...
        </p>
    </div>
</x-layouts.app>