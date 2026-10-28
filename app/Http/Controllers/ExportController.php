<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Obat;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;

class ExportController extends Controller
{
    // ============================================
    // 1. EXPORT DATA DOKTER
    // ============================================

    public function dokter()
    {
        $dokters = User::with('poli')
            ->where('role', 'dokter')
            ->orderBy('nama', 'asc')
            ->get();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $sheet->setTitle('Data Dokter');

        $headers = [
            'No',
            'Nama Dokter',
            'Email',
            'No. KTP',
            'No. HP',
            'Alamat',
            'Poli',
        ];

        foreach ($headers as $index => $header) {
            $sheet->setCellValue(
                [$index + 1, 1],
                $header
            );
        }

        $row = 2;

        foreach ($dokters as $index => $dokter) {

            $sheet->setCellValue('A' . $row, $index + 1);

            $data = [
                'B' => $dokter->nama,
                'C' => $dokter->email,
                'D' => $dokter->no_ktp,
                'E' => $dokter->no_hp,
                'F' => $dokter->alamat,
                'G' => $dokter->poli->nama_poli
                    ?? 'Belum Dipilih',
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

        $this->formatExcel(
            $sheet,
            'G',
            max(1, $row - 1),
            [
                'A' => 8,
                'B' => 28,
                'C' => 32,
                'D' => 24,
                'E' => 20,
                'F' => 45,
                'G' => 25,
            ]
        );

        return $this->downloadExcel(
            $spreadsheet,
            'data-dokter'
        );
    }

    // ============================================
    // 2. EXPORT DATA PASIEN
    // ============================================

    public function pasien()
    {
        $pasiens = User::where('role', 'pasien')
            ->orderBy('nama', 'asc')
            ->get();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $sheet->setTitle('Data Pasien');

        $headers = [
            'No',
            'Nama Pasien',
            'Email',
            'No. KTP',
            'No. HP',
            'Alamat',
        ];

        foreach ($headers as $index => $header) {
            $sheet->setCellValue(
                [$index + 1, 1],
                $header
            );
        }

        $row = 2;

        foreach ($pasiens as $index => $pasien) {

            $sheet->setCellValue('A' . $row, $index + 1);

            $data = [
                'B' => $pasien->nama,
                'C' => $pasien->email,
                'D' => $pasien->no_ktp,
                'E' => $pasien->no_hp,
                'F' => $pasien->alamat,
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

        $this->formatExcel(
            $sheet,
            'F',
            max(1, $row - 1),
            [
                'A' => 8,
                'B' => 30,
                'C' => 35,
                'D' => 25,
                'E' => 22,
                'F' => 45,
            ]
        );

        return $this->downloadExcel(
            $spreadsheet,
            'data-pasien'
        );
    }

    // ============================================
    // 3. EXPORT DATA OBAT + STOK
    // ============================================

    public function obat()
    {
        $obats = Obat::orderBy('nama_obat', 'asc')
            ->get();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $sheet->setTitle('Data Obat');

        // HEADER EXCEL
        $headers = [
            'No',
            'Nama Obat',
            'Kemasan',
            'Stok',
            'Harga (Rp)',
        ];

        foreach ($headers as $index => $header) {
            $sheet->setCellValue(
                [$index + 1, 1],
                $header
            );
        }

        // ISI DATA
        $row = 2;

        foreach ($obats as $index => $obat) {

            $sheet->setCellValue(
                'A' . $row,
                $index + 1
            );

            // Nama Obat
            $sheet->setCellValueExplicit(
                'B' . $row,
                (string) ($obat->nama_obat ?? '-'),
                DataType::TYPE_STRING
            );

            // Kemasan
            $sheet->setCellValueExplicit(
                'C' . $row,
                (string) ($obat->kemasan ?? '-'),
                DataType::TYPE_STRING
            );

            // Stok sebagai angka
            $sheet->setCellValue(
                'D' . $row,
                (int) ($obat->stok ?? 0)
            );

            // Harga sebagai angka
            $sheet->setCellValue(
                'E' . $row,
                (int) ($obat->harga ?? 0)
            );

            $row++;
        }

        // FORMAT TABEL
        $this->formatExcel(
            $sheet,
            'E',
            max(1, $row - 1),
            [
                'A' => 8,
                'B' => 35,
                'C' => 25,
                'D' => 18,
                'E' => 25,
            ]
        );

        // FORMAT STOK DAN HARGA
        if ($row > 2) {

            // Stok angka bulat
            $sheet->getStyle(
                'D2:D' . ($row - 1)
            )
                ->getNumberFormat()
                ->setFormatCode('#,##0');

            // Harga Rupiah
            $sheet->getStyle(
                'E2:E' . ($row - 1)
            )
                ->getNumberFormat()
                ->setFormatCode('"Rp" #,##0');
        }

        return $this->downloadExcel(
            $spreadsheet,
            'data-obat'
        );
    }

    // ============================================
    // FORMAT TABEL EXCEL
    // ============================================

    private function formatExcel(
        $sheet,
        string $lastColumn,
        int $lastRow,
        array $widths
    ) {
        $headerRange = 'A1:' . $lastColumn . '1';
        $tableRange = 'A1:' . $lastColumn . $lastRow;

        // Header tebal
        $sheet->getStyle($headerRange)
            ->getFont()
            ->setBold(true);

        // Warna hijau muda
        $sheet->getStyle($headerRange)
            ->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()
            ->setARGB('FFD1FAE5');

        // Ukuran kolom
        foreach ($widths as $column => $width) {
            $sheet->getColumnDimension($column)
                ->setWidth($width);
        }

        // Border tabel
        $sheet->getStyle($tableRange)
            ->getBorders()
            ->getAllBorders()
            ->setBorderStyle(Border::BORDER_THIN);

        // Filter data
        $sheet->setAutoFilter($tableRange);

        // Header tetap terlihat
        $sheet->freezePane('A2');
    }

    // ============================================
    // DOWNLOAD EXCEL
    // ============================================

    private function downloadExcel(
        Spreadsheet $spreadsheet,
        string $namaFile
    ) {
        $fileName = $namaFile . '-'
            . now()->format('Y-m-d-His')
            . '.xlsx';

        $tempFile = tempnam(
            sys_get_temp_dir(),
            'poliklinik_excel_'
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
        // PEMBERSIHAN OUTPUT BUFFER
        // ========================================

        // Metode ini dipertahankan karena
        // sebelumnya berhasil memperbaiki
        // masalah file Excel yang rusak.

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

        if (headers_sent($source, $line)) {

            @unlink($tempFile);

            throw new \RuntimeException(
                'Output sudah dikirim dari '
                . $source . ':' . $line
            );
        }

        // ========================================
        // RESPONSE DOWNLOAD
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
