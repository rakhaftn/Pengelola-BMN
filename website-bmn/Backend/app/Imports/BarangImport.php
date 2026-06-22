<?php

namespace App\Imports;

use App\Models\Barang;
use App\Models\KategoriBarang;
use App\Models\Lokasi;

class BarangImport
{
    public function import($filePath): array
    {
        $results = ['created' => 0, 'updated' => 0, 'errors' => [], 'rows' => 0];

        if (!file_exists($filePath)) {
            $results['errors'][] = "File tidak ditemukan: " . $filePath;
            return $results;
        }

        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));

        if ($extension === 'csv') {
            return $this->importCsv($filePath, $results);
        }

        // Try to read as Excel
        try {
            return $this->importExcel($filePath, $results);
        } catch (\Exception $e) {
            $results['errors'][] = "Error membaca file: " . $e->getMessage();
            return $results;
        }
    }

    private function importExcel(string $filePath, array &$results): array
    {
        $zip = new \ZipArchive();
        if ($zip->open($filePath) !== true) {
            // Try as CSV if zip fails
            return $this->importCsv($filePath, $results);
        }

        // Get shared strings
        $strings = [];
        if ($zip->locateName('xl/sharedStrings.xml') !== false) {
            $ss = $zip->getFromName('xl/sharedStrings.xml');
            preg_match_all('/<t[^>]*>([^<]*)<\/t>/', $ss, $matches);
            $strings = $matches[1];
        }

        // Get first sheet
        $sheet = $zip->locateName('xl/worksheets/sheet1.xml');
        if ($sheet === false) {
            $zip->close();
            $results['errors'][] = 'Sheet tidak ditemukan';
            return $results;
        }
        $sheetContent = $zip->getFromName($sheet);
        $zip->close();

        // Parse sheet using regex
        $rows = $this->parseSheetRows($sheetContent, $strings);

        if (empty($rows)) {
            $results['errors'][] = 'Tidak ada data yang ditemukan';
            return $results;
        }

        // First row is header
        $headers = array_shift($rows);
        $results['rows'] = count($rows);

        foreach ($rows as $rowData) {
            try {
                $data = $this->mapRowToData($headers, $rowData);
                if (!empty($data)) {
                    $this->processBarang($data, $results);
                }
            } catch (\Exception $e) {
                $results['errors'][] = 'Error baris: ' . $e->getMessage();
            }
        }

        return $results;
    }

    private function parseSheetRows(string $sheetContent, array $strings): array
    {
        $rows = [];

        // Find all rows
        preg_match_all('/<row[^>]*r="(\d+)"[^>]*>(.*?)<\/row>/s', $sheetContent, $rowMatches, PREG_SET_ORDER);

        foreach ($rowMatches as $rowMatch) {
            $rowNum = (int) $rowMatch[1];
            $rowContent = $rowMatch[2];

            $cells = [];

            // Find all cells with their references
            preg_match_all('/<c r="([A-Z]+' . $rowNum . ')"([^>]*)>(.*?)<\/c>/s', $rowContent, $cellMatches, PREG_SET_ORDER);

            foreach ($cellMatches as $cellMatch) {
                $cellRef = $cellMatch[1];
                $cellContent = $cellMatch[3];

                // Check if it's a shared string reference
                if (strpos($cellMatch[2], 't="s"') !== false) {
                    preg_match('/<v>(\d+)<\/v>/', $cellContent, $vMatch);
                    if ($vMatch) {
                        $colIndex = $this->columnLetterToIndex($cellRef);
                        $cells[$colIndex] = $strings[(int) $vMatch[1]] ?? '';
                    }
                } else {
                    // Inline value
                    preg_match('/<v>([^<]*)<\/v>/', $cellContent, $vMatch);
                    if ($vMatch) {
                        $colIndex = $this->columnLetterToIndex($cellRef);
                        $cells[$colIndex] = $vMatch[1];
                    }
                }
            }

            if (!empty($cells)) {
                ksort($cells);
                $rows[] = array_values($cells);
            }
        }

        return $rows;
    }

    private function columnLetterToIndex(string $col): int
    {
        $col = preg_replace('/[0-9]/', '', $col);
        $index = 0;
        for ($i = 0; $i < strlen($col); $i++) {
            $index = $index * 26 + (ord($col[$i]) - ord('A') + 1);
        }
        return $index - 1;
    }

    private function mapRowToData(array $headers, array $row): array
    {
        $data = [];

        // Map headers to find columns
        $headerLower = array_map('strtolower', $headers);

        $fieldMapping = [
            'nama' => ['nama barang', 'nama_barang', 'name', 'deskripsi', 'barang'],
            'merek' => ['merk', 'merek', 'brand', 'tipe'],
            'kategori' => ['kategori', 'jenis', 'category', 'jenis bmn'],
            'kondisi' => ['kondisi', 'condition', 'status kondisi'],
            'status' => ['status bmn', 'status', 'status penggunaan'],
            'lokasi' => ['lokasi', 'lokasi ruang', 'ruang', 'ruangan'],
            'nilai' => ['nilai perolehan', 'nilai', 'harga', 'nilai_perolehan'],
            'tahun' => ['tahun', 'tahun perolehan', 'tahun_perolehan'],
        ];

        foreach ($fieldMapping as $field => $keywords) {
            foreach ($keywords as $keyword) {
                foreach ($headerLower as $i => $h) {
                    if (strpos($h, $keyword) !== false && isset($row[$i])) {
                        $data[$field] = trim($row[$i]);
                        break 2;
                    }
                }
            }
        }

        return $data;
    }

    private function processBarang(array $data, array &$results): void
    {
        if (empty($data['nama'])) {
            return;
        }

        // Find or create kategori
        $kategoriId = null;
        if (!empty($data['kategori'])) {
            $kategori = KategoriBarang::firstOrCreate(
                ['nama' => $data['kategori']],
                ['keterangan' => 'Imported']
            );
            $kategoriId = $kategori->id;
        }

        // Find or create lokasi
        $lokasiId = null;
        if (!empty($data['lokasi'])) {
            $lokasi = Lokasi::firstOrCreate(
                ['nama' => $data['lokasi']],
                ['keterangan' => 'Imported']
            );
            $lokasiId = $lokasi->id;
        }

        // Map kondisi
        $kondisi = $this->mapKondisi($data['kondisi'] ?? '');

        // Map status
        $status = $this->mapStatus($data['status'] ?? '');

        // Check if exists by nama
        $existing = Barang::where('nama', $data['nama'])->first();

        if ($existing) {
            $existing->update([
                'kategori_id' => $kategoriId ?? $existing->kategori_id,
                'merek' => $data['merek'] ?? $existing->merek,
                'kondisi' => $kondisi,
                'status' => $status,
                'lokasi_id' => $lokasiId ?? $existing->lokasi_id,
                'nilai_perolehan' => is_numeric($data['nilai'] ?? '') ? $data['nilai'] : null,
                'tahun_perolehan' => is_numeric($data['tahun'] ?? '') ? $data['tahun'] : null,
            ]);
            $results['updated']++;
        } else {
            Barang::create([
                'kode_barang' => Barang::generateKode(),
                'nama' => $data['nama'],
                'kategori_id' => $kategoriId,
                'merek' => $data['merek'] ?? null,
                'kondisi' => $kondisi,
                'status' => $status,
                'lokasi_id' => $lokasiId,
                'nilai_perolehan' => is_numeric($data['nilai'] ?? '') ? $data['nilai'] : null,
                'tahun_perolehan' => is_numeric($data['tahun'] ?? '') ? $data['tahun'] : date('Y'),
            ]);
            $results['created']++;
        }
    }

    private function mapKondisi(string $value): string
    {
        $value = strtolower($value);
        if (strpos($value, 'rusak berat') !== false || strpos($value, 'berat') !== false) return 'rusak_berat';
        if (strpos($value, 'rusak') !== false || strpos($value, 'ringan') !== false) return 'rusak_ringan';
        return 'baik';
    }

    private function mapStatus(string $value): string
    {
        $value = strtolower($value);
        if (strpos($value, 'digunakan') !== false || strpos($value, 'aktif') !== false || strpos($value, 'sedang') !== false) return 'dipinjam';
        if (strpos($value, 'perbaikan') !== false || strpos($value, 'maintenance') !== false) return 'dalam_perawatan';
        if (strpos($value, 'hapus') !== false || strpos($value, 'non') !== false) return 'dihapuskan';
        return 'tersedia';
    }

    private function importCsv(string $filePath, array &$results): array
    {
        $handle = fopen($filePath, 'r');
        if (!$handle) {
            $results['errors'][] = 'Tidak dapat membuka file';
            return $results;
        }

        $headers = fgetcsv($handle);
        if (!$headers) {
            fclose($handle);
            $results['errors'][] = 'Header tidak ditemukan';
            return $results;
        }

        $rowCount = 0;
        while (($row = fgetcsv($handle)) !== false) {
            $rowCount++;
            try {
                $data = $this->mapRowToData($headers, $row);
                if (!empty($data)) {
                    $this->processBarang($data, $results);
                }
            } catch (\Exception $e) {
                $results['errors'][] = "Baris $rowCount: " . $e->getMessage();
            }
        }

        $results['rows'] = $rowCount;
        fclose($handle);
        return $results;
    }
}
