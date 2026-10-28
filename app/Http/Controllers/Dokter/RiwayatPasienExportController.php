<?php

namespace App\Http\Controllers\Dokter;

use App\Http\Controllers\Controller;
use App\Models\Periksa;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class RiwayatPasienExportController extends Controller
{
    // ============================================
    // EXPORT EXCEL RIWAYAT PASIEN
    // ============================================

    public function __invoke()
    {
        // ========================================
        // AMBIL RIWAYAT MILIK DOKTER LOGIN
        // ========================================

        $riwayatPasien = Periksa::query()
            ->with([
                'daftarPoli.pasien',
            ])
            ->whereHas(
                'daftarPoli.jadwalPeriksa',
                function (Builder $query): void {
                    $query->where(
                        'id_dokter',
                        Auth::id()
                    );
                }
            )
            ->orderByDesc('tgl_periksa')
            ->orderByDesc('id')
            ->get();

        // ========================================
        // MEMBUAT FILE EXCEL
        // ========================================

        $spreadsheet = new Spreadsheet();

        $sheet = $spreadsheet->getActiveSheet();

        $sheet->setTitle('Riwayat Pasien');

        // ========================================
        // HEADER EXCEL
        // ========================================

        $headers = [
            'No',
            'No Antrian',
            'Nama Pasien',
            'Keluhan',
            'Tanggal Periksa',
            'Biaya Pemeriksaan',
        ];

        foreach ($headers as $index => $header) {
            $sheet->setCellValue(
                [$index + 1, 1],
                $header
            );
        }

        // ========================================
        // ISI DATA RIWAYAT PASIEN
        // ========================================

        $row = 2;

        foreach ($riwayatPasien as $index => $riwayat) {

            $pendaftaran = $riwayat->daftarPoli;

            $tanggal = $riwayat->tgl_periksa
                ? Carbon::parse(
                    $riwayat->tgl_periksa
                )->format('d/m/Y')
                : '-';

            $sheet->setCellValue(
                'A' . $row,
                $index + 1
            );

            $data = [
                'B' => $pendaftaran?->no_antrian,
                'C' => $pendaftaran?->pasien?->nama,
                'D' => $pendaftaran?->keluhan,
                'E' => $tanggal,
            ];

            foreach ($data as $column => $value) {

                $sheet->setCellValueExplicit(
                    $column . $row,
                    (string) ($value ?? '-'),
                    DataType::TYPE_STRING
                );
            }

            // Biaya disimpan sebagai angka Excel
            $sheet->setCellValue(
                'F' . $row,
                (float) ($riwayat->biaya_periksa ?? 0)
            );

            $row++;
        }

        // ========================================
        // FORMAT HEADER
        // ========================================

        $sheet->getStyle('A1:F1')
            ->getFont()
            ->setBold(true);

        $sheet->getStyle('A1:F1')
            ->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()
            ->setARGB('FFD1FAE5');

        $sheet->getStyle('A1:F1')
            ->getAlignment()
            ->setVertical(
                Alignment::VERTICAL_CENTER
            );

        $sheet->getRowDimension(1)
            ->setRowHeight(25);

        // ========================================
        // LEBAR KOLOM
        // ========================================

        $widths = [
            'A' => 8,
            'B' => 18,
            'C' => 30,
            'D' => 50,
            'E' => 22,
            'F' => 25,
        ];

        foreach ($widths as $column => $width) {

            $sheet->getColumnDimension($column)
                ->setWidth($width);
        }

        // ========================================
        // FORMAT TABEL
        // ========================================

        $lastRow = max(1, $row - 1);

        $sheet->getStyle('A1:F' . $lastRow)
            ->getBorders()
            ->getAllBorders()
            ->setBorderStyle(
                Border::BORDER_THIN
            );

        // Format Rupiah
        if ($row > 2) {

            $sheet->getStyle(
                'F2:F' . ($row - 1)
            )
                ->getNumberFormat()
                ->setFormatCode('"Rp" #,##0');

            // Keluhan dapat ditampilkan
            // dalam beberapa baris
            $sheet->getStyle(
                'D2:D' . ($row - 1)
            )
                ->getAlignment()
                ->setWrapText(true);
        }

        // Filter Excel
        $sheet->setAutoFilter(
            'A1:F' . $lastRow
        );

        // Bekukan header
        $sheet->freezePane('A2');

        // ========================================
        // NAMA FILE EXCEL
        // ========================================

        $fileName = 'riwayat-pasien-'
            . now()->format('Y-m-d-His')
            . '.xlsx';

        // ========================================
        // SIMPAN FILE SEMENTARA
        // ========================================

        $tempFile = tempnam(
            sys_get_temp_dir(),
            'riwayat_excel_'
        );

        if ($tempFile === false) {

            abort(
                500,
                'Gagal membuat file sementara.'
            );
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

        // Menggunakan metode download Excel
        // yang sudah berhasil sebelumnya.

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

        // ========================================
        // PERIKSA HEADER HTTP
        // ========================================

        if (headers_sent($source, $line)) {

            @unlink($tempFile);

            throw new \RuntimeException(
                'Output sudah dikirim dari '
                . $source . ':' . $line
            );
        }

        // ========================================
        // DOWNLOAD FILE EXCEL
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
}
