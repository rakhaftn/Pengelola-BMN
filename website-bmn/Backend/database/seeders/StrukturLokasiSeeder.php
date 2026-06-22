<?php

namespace Database\Seeders;

use App\Models\Direktorat;
use App\Models\Gedung;
use App\Models\Lantai;
use App\Models\Lokasi;
use App\Models\Ruangan;
use Illuminate\Database\Seeder;

class StrukturLokasiSeeder extends Seeder
{
    public function run(): void
    {
        // Buat struktur lokasi contoh
        $direktorat = Direktorat::firstOrCreate(
            ['kode' => 'DIR-001'],
            [
                'nama' => 'Direktorat Umum',
                'kepala' => 'Dr. Ahmad Dahlan',
                'keterangan' => 'Direktorat Umum sebagai contoh',
                'is_active' => true,
            ]
        );

        $gedung = Gedung::firstOrCreate(
            ['kode' => 'GDG-A'],
            [
                'direktorat_id' => $direktorat->id,
                'nama' => 'Gedung A',
                'alamat' => 'Jl. Raya Contoh No. 1',
                'keterangan' => 'Gedung utama',
                'is_active' => true,
            ]
        );

        $lantai1 = Lantai::firstOrCreate(
            ['kode' => 'LT-1'],
            [
                'gedung_id' => $gedung->id,
                'nama' => 'Lantai 1',
                'lantai_ke' => 1,
                'keterangan' => 'Lantai dasar',
                'is_active' => true,
            ]
        );

        $lantai2 = Lantai::firstOrCreate(
            ['kode' => 'LT-2'],
            [
                'gedung_id' => $gedung->id,
                'nama' => 'Lantai 2',
                'lantai_ke' => 2,
                'keterangan' => 'Lantai pertama',
                'is_active' => true,
            ]
        );

        // Lokasi untuk setiap lantai
        $lokasi1 = Lokasi::firstOrCreate(
            ['kode' => 'LOK-101'],
            [
                'lantai_id' => $lantai1->id,
                'nama' => 'Ruang Server',
                'alamat' => 'Lantai 1, Gedung A',
                'keterangan' => 'Ruang server utama',
                'is_active' => true,
            ]
        );

        $lokasi2 = Lokasi::firstOrCreate(
            ['kode' => 'LOK-102'],
            [
                'lantai_id' => $lantai1->id,
                'nama' => 'Ruang Rapat',
                'alamat' => 'Lantai 1, Gedung A',
                'keterangan' => 'Ruang rapat umum',
                'is_active' => true,
            ]
        );

        $lokasi3 = Lokasi::firstOrCreate(
            ['kode' => 'LOK-201'],
            [
                'lantai_id' => $lantai2->id,
                'nama' => 'Ruang kerja 1',
                'alamat' => 'Lantai 2, Gedung A',
                'keterangan' => 'Ruang kerja karyawan',
                'is_active' => true,
            ]
        );

        // Ruangan
        Ruangan::firstOrCreate(
            ['kode' => 'RNG-001'],
            [
                'lokasi_id' => $lokasi1->id,
                'nama' => 'Ruang Server Utama',
                'keterangan' => 'Tempat server dan infrastruktur IT',
                'is_active' => true,
            ]
        );

        Ruangan::firstOrCreate(
            ['kode' => 'RNG-002'],
            [
                'lokasi_id' => $lokasi2->id,
                'nama' => 'Ruang Rapat Utama',
                'keterangan' => 'Ruang rapat untuk 20 orang',
                'is_active' => true,
            ]
        );

        Ruangan::firstOrCreate(
            ['kode' => 'RNG-003'],
            [
                'lokasi_id' => $lokasi3->id,
                'nama' => 'Ruang Kerja Staff',
                'keterangan' => 'Ruang kerja bersama',
                'is_active' => true,
            ]
        );

        $this->command->info('Struktur Lokasi berhasil dibuat!');
        $this->command->info("- Direktorat: {$direktorat->nama}");
        $this->command->info("- Gedung: {$gedung->nama}");
        $this->command->info("- Lantai: {$lantai1->nama}, {$lantai2->nama}");
        $this->command->info("- Lokasi: {$lokasi1->nama}, {$lokasi2->nama}, {$lokasi3->nama}");
    }
}
