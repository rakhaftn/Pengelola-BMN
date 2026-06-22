<?php

namespace App\Imports;

use App\Models\User;
use App\Models\UnitKerja;
use Illuminate\Support\Facades\Hash;

class PegawaiImport
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
            return $this->importCsv($filePath, $results);
        }

        $strings = [];
        if ($zip->locateName('xl/sharedStrings.xml') !== false) {
            $ss = $zip->getFromName('xl/sharedStrings.xml');
            preg_match_all('/<t[^>]*>([^<]*)<\/t>/', $ss, $matches);
            $strings = $matches[1];
        }

        $sheet = $zip->locateName('xl/worksheets/sheet1.xml');
        if ($sheet === false) {
            $zip->close();
            $results['errors'][] = 'Sheet tidak ditemukan';
            return $results;
        }
        $sheetContent = $zip->getFromName($sheet);
        $zip->close();

        $rows = $this->parseSheetRows($sheetContent, $strings);

        if (empty($rows)) {
            $results['errors'][] = 'Tidak ada data';
            return $results;
        }

        $headers = array_shift($rows);
        $results['rows'] = count($rows);

        foreach ($rows as $rowData) {
            try {
                $data = $this->mapRowToData($headers, $rowData);
                if (!empty($data)) {
                    $this->processUser($data, $results);
                }
            } catch (\Exception $e) {
                $results['errors'][] = 'Error: ' . $e->getMessage();
            }
        }

        return $results;
    }

    private function parseSheetRows(string $sheetContent, array $strings): array
    {
        $rows = [];
        preg_match_all('/<row[^>]*r="(\d+)"[^>]*>(.*?)<\/row>/s', $sheetContent, $rowMatches, PREG_SET_ORDER);

        foreach ($rowMatches as $rowMatch) {
            $rowNum = (int) $rowMatch[1];
            $rowContent = $rowMatch[2];
            $cells = [];

            preg_match_all('/<c r="([A-Z]+' . $rowNum . ')"([^>]*)>(.*?)<\/c>/s', $rowContent, $cellMatches, PREG_SET_ORDER);

            foreach ($cellMatches as $cellMatch) {
                $cellRef = $cellMatch[1];
                $cellContent = $cellMatch[3];

                if (strpos($cellMatch[2], 't="s"') !== false) {
                    preg_match('/<v>(\d+)<\/v>/', $cellContent, $vMatch);
                    if ($vMatch) {
                        $colIndex = $this->columnLetterToIndex($cellRef);
                        $cells[$colIndex] = $strings[(int) $vMatch[1]] ?? '';
                    }
                } else {
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
        $headerLower = array_map('strtolower', $headers);

        $fieldMapping = [
            'name' => ['nama', 'name', 'nama lengkap', 'pegawai', 'nama_lengkap'],
            'nip' => ['nip', 'nik', 'no nip', 'nomor nip', 'no_nip'],
            'email' => ['email', 'mail', 'e-mail'],
            'jabatan' => ['jabatan', 'position', 'posisi', 'golongan'],
            'unit_kerja' => ['unit kerja', 'unit_kerja', 'unit', 'bagian', 'direktorat'],
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

    private function processUser(array $data, array &$results): void
    {
        if (empty($data['name'])) {
            return;
        }

        // Find or create unit kerja
        $unitKerjaId = null;
        if (!empty($data['unit_kerja'])) {
            $unitKerja = UnitKerja::firstOrCreate(
                ['nama' => $data['unit_kerja']],
                ['keterangan' => 'Imported']
            );
            $unitKerjaId = $unitKerja->id;
        }

        // Generate email
        $email = $data['email'] ?? null;
        if (!$email && !empty($data['nip'])) {
            $email = strtolower($data['nip']) . '@bmn.go.id';
        }

        // Check existing
        $query = User::query();
        if (!empty($data['nip'])) {
            $query->where('nip', $data['nip']);
        } elseif ($email) {
            $query->where('email', $email);
        }
        $existing = $query->first();

        if ($existing) {
            $existing->update([
                'name' => $data['name'],
                'jabatan' => $data['jabatan'] ?? $existing->jabatan,
                'unit_kerja_id' => $unitKerjaId ?? $existing->unit_kerja_id,
            ]);
            $results['updated']++;
        } else {
            User::create([
                'name' => $data['name'],
                'nip' => $data['nip'] ?? null,
                'email' => $email ?? 'user' . time() . '@bmn.go.id',
                'password' => Hash::make('password123'),
                'jabatan' => $data['jabatan'] ?? null,
                'unit_kerja_id' => $unitKerjaId,
                'is_active' => true,
            ]);
            $results['created']++;
        }
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
                    $this->processUser($data, $results);
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
