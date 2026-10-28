<?php

namespace App\Http\Controllers\Dokter;

use App\Http\Controllers\Controller;
use App\Models\JadwalPeriksa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;

class JadwalPeriksaController extends Controller
{
    // ============================================
    // MENAMPILKAN JADWAL PERIKSA
    // ============================================

    public function index()
    {
        $dokter = Auth::user();

        $jadwalPeriksas = JadwalPeriksa::where(
            'id_dokter',
            $dokter->id
        )
            ->orderBy('hari')
            ->get();

        return view(
            'dokter.jadwal-periksa.index',
            compact('jadwalPeriksas')
        );
    }

    // ============================================
    // FORM TAMBAH JADWAL
    // ============================================

    public function create()
    {
        return view('dokter.jadwal-periksa.create');
    }

    // ============================================
    // SIMPAN JADWAL
    // ============================================

    public function store(Request $request)
    {
        $request->validate([
            'hari' => 'required',
            'jam_mulai' => 'required',
            'jam_selesai' => 'required',
        ]);

        JadwalPeriksa::create([
            'id_dokter' => Auth::id(),
            'hari' => $request->hari,
            'jam_mulai' => $request->jam_mulai,
            'jam_selesai' => $request->jam_selesai,
        ]);

        return redirect()
            ->route('jadwal-periksa.index')
            ->with('message', 'Data Berhasil di Simpan')
            ->with('type', 'success');
    }

    // ============================================
    // FORM EDIT JADWAL
    // ============================================

    public function edit($id)
    {
        $jadwalPeriksa = JadwalPeriksa::where(
            'id_dokter',
            Auth::id()
        )->findOrFail($id);

        return view(
            'dokter.jadwal-periksa.edit',
            compact('jadwalPeriksa')
        );
    }

    // ============================================
    // UPDATE JADWAL
    // ============================================

    public function update(Request $request, string $id)
    {
        $request->validate([
            'hari' => 'required',
            'jam_mulai' => 'required',
            'jam_selesai' => 'required',
        ]);

        $jadwalPeriksa = JadwalPeriksa::where(
            'id_dokter',
            Auth::id()
        )->findOrFail($id);

        $jadwalPeriksa->update([
            'hari' => $request->hari,
            'jam_mulai' => $request->jam_mulai,
            'jam_selesai' => $request->jam_selesai,
        ]);

        return redirect()
            ->route('jadwal-periksa.index')
            ->with('message', 'Berhasil Melakukan Update Data')
            ->with('type', 'success');
    }

    // ============================================
    // HAPUS JADWAL
    // ============================================

    public function destroy(string $id)
    {
        $jadwalPeriksa = JadwalPeriksa::where(
            'id_dokter',
            Auth::id()
        )->findOrFail($id);

        $jadwalPeriksa->delete();

        return redirect()
            ->route('jadwal-periksa.index')
            ->with('message', 'Berhasil Melakukan Hapus Data')
            ->with('type', 'success');
    }

    // ============================================
    // EXPORT EXCEL JADWAL PERIKSA
    // ============================================

    public function exportExcel()
    {
        // Hanya mengambil jadwal milik
        // dokter yang sedang login.

        $dokter = Auth::user();

        $jadwalPeriksas = JadwalPeriksa::where(
            'id_dokter',
            $dokter->id
        )
            ->orderBy('hari')
            ->orderBy('jam_mulai')
            ->get();

        // Membuat spreadsheet
        $spreadsheet = new Spreadsheet();

        $sheet = $spreadsheet->getActiveSheet();

        $sheet->setTitle('Jadwal Periksa');

        // ========================================
        // HEADER EXCEL
        // ========================================

        $headers = [
            'No',
            'Nama Dokter',
            'Hari',
            'Jam Mulai',
            'Jam Selesai',
        ];

        foreach ($headers as $index => $header) {
            $sheet->setCellValue(
                [$index + 1, 1],
                $header
            );
        }

        // ========================================
        // ISI DATA EXCEL
        // ========================================

        $row = 2;

        foreach ($jadwalPeriksas as $index => $jadwal) {

            $sheet->setCellValue(
                'A' . $row,
                $index + 1
            );

            $data = [
                'B' => $dokter->nama,
                'C' => $jadwal->hari,
                'D' => $this->formatJam(
                    $jadwal->jam_mulai
                ),
                'E' => $this->formatJam(
                    $jadwal->jam_selesai
                ),
            ];

            foreach ($data as $column => $value) {

                $sheet->setCellValueExplicit(
                    $column . $row,
                    (string) ($value ?? '-'),
                    DataType::TYPE_STRING
                );
            }

            $row++;
        }

        // ========================================
        // FORMAT TABEL EXCEL
        // ========================================

        $lastRow = max(1, $row - 1);

        // Header tebal
        $sheet->getStyle('A1:E1')
            ->getFont()
            ->setBold(true);

        // Warna hijau muda
        $sheet->getStyle('A1:E1')
            ->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()
            ->setARGB('FFD1FAE5');

        // Lebar kolom
        $widths = [
            'A' => 8,
            'B' => 30,
            'C' => 20,
            'D' => 20,
            'E' => 20,
        ];

        foreach ($widths as $column => $width) {
            $sheet->getColumnDimension($column)
                ->setWidth($width);
        }

        // Border tabel
        $sheet->getStyle('A1:E' . $lastRow)
            ->getBorders()
            ->getAllBorders()
            ->setBorderStyle(Border::BORDER_THIN);

        // Filter Excel
        $sheet->setAutoFilter(
            'A1:E' . $lastRow
        );

        // Header tetap terlihat
        $sheet->freezePane('A2');

        // ========================================
        // BUAT FILE EXCEL
        // ========================================

        $fileName = 'jadwal-periksa-'
            . now()->format('Y-m-d-His')
            . '.xlsx';

        $tempFile = tempnam(
            sys_get_temp_dir(),
            'jadwal_excel_'
        );

        if ($tempFile === false) {
            abort(500, 'Gagal membuat file sementara.');
        }

        try {
            $writer = new Xlsx($spreadsheet);
            $writer->save($tempFile);
        } catch (\Throwable $e) {
            @unlink($tempFile);
            throw $e;
        } finally {
            $spreadsheet->disconnectWorksheets();
        }

        // ========================================
        // BERSIHKAN OUTPUT BUFFER
        // ========================================

        // Menggunakan metode yang sebelumnya
        // berhasil untuk ekspor Excel.

        while (ob_get_level() > 0) {

            $status = ob_get_status();

            $flags = $status['flags'] ?? 0;

            if (
                ($flags & PHP_OUTPUT_HANDLER_REMOVABLE) !== 0
            ) {
                ob_end_clean();
                continue;
            }

            if (
                ($flags & PHP_OUTPUT_HANDLER_CLEANABLE) !== 0
            ) {
                ob_clean();
            }

            break;
        }

        // Memastikan header HTTP belum dikirim
        if (headers_sent($source, $line)) {

            @unlink($tempFile);

            throw new \RuntimeException(
                'Output sudah dikirim dari '
                . $source . ':' . $line
            );
        }

        // ========================================
        // DOWNLOAD EXCEL
        // ========================================

        return response()->download(
            $tempFile,
            $fileName,
            [
                'Content-Type' =>
                    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',

                'Cache-Control' =>
                    'no-store, no-cache, must-revalidate',
            ]
        )->deleteFileAfterSend(true);
    }

    // ============================================
    // FORMAT JAM
    // ============================================

    private function formatJam($jam)
    {
        if (empty($jam)) {
            return '-';
        }

        if ($jam instanceof \DateTimeInterface) {
            return $jam->format('H:i');
        }

        // Mengubah 08:00:00 menjadi 08:00
        return substr((string) $jam, 0, 5);
    }
}
