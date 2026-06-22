<?php

namespace Database\Seeders;

use App\Models\Barang;
use App\Models\KategoriBarang;
use App\Models\Lokasi;
use App\Models\Ruangan;
use App\Models\UnitKerja;
use App\Models\User;
use App\Services\QrCodeService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        // Unit Kerja
        $sekret = UnitKerja::firstOrCreate(['kode' => 'UK-001'], ['nama' => 'Sekretariat', 'kepala' => 'Drs. Budi Santoso']);
        $umum   = UnitKerja::firstOrCreate(['kode' => 'UK-002'], ['nama' => 'Bagian Umum & Perlengkapan', 'kepala' => 'Siti Aminah, S.E.']);
        $ti     = UnitKerja::firstOrCreate(['kode' => 'UK-003'], ['nama' => 'Bidang Teknologi Informasi', 'kepala' => 'Andi Wijaya, S.Kom.']);

        // Users
        // Super Admin - Karyawan BMN Kantor (monitoring & ijin peminjaman)
        $superAdmin = User::firstOrCreate(
            ['email' => 'superadmin@bmn.go.id'],
            ['name' => 'Super Administrator', 'nip' => '198001012005011001', 'password' => Hash::make('password'), 'unit_kerja_id' => $ti->id, 'jabatan' => 'Administrator Sistem', 'is_active' => true]
        );
        $superAdmin->syncRoles(['super_admin']);

        // Staff - Karyawan BMN Gudang (konfirmasi stock, kondisi, bukti surat, serah terima)
        $staff = User::firstOrCreate(
            ['email' => 'staff@bmn.go.id'],
            ['name' => 'Rina Staff BMN', 'nip' => '198505102010012002', 'password' => Hash::make('password'), 'unit_kerja_id' => $umum->id, 'jabatan' => 'Staff Gudang BMN', 'is_active' => true]
        );
        $staff->syncRoles(['staff']);

        // User - Karyawan Kemenkeu (mengajukan peminjaman via surat)
        $user = User::firstOrCreate(
            ['email' => 'user@bmn.go.id'],
            ['name' => 'Joko Karyawan', 'nip' => '199002202015011004', 'password' => Hash::make('password'), 'unit_kerja_id' => $ti->id, 'jabatan' => 'Staf', 'is_active' => true]
        );
        $user->syncRoles(['user']);

        // Kategori
        $elektronik = KategoriBarang::firstOrCreate(['kode' => 'KAT-001'], ['nama' => 'Elektronik']);
        $mebel      = KategoriBarang::firstOrCreate(['kode' => 'KAT-002'], ['nama' => 'Mebelair']);
        $kendaraan  = KategoriBarang::firstOrCreate(['kode' => 'KAT-003'], ['nama' => 'Kendaraan']);

        // Lokasi & Ruangan
        $gedungA = Lokasi::firstOrCreate(['kode' => 'LOK-001'], ['nama' => 'Gedung A - Kantor Pusat', 'alamat' => 'Jl. Merdeka No. 1']);
        $r101 = Ruangan::firstOrCreate(['kode' => 'RNG-101'], ['lokasi_id' => $gedungA->id, 'nama' => 'Ruang Rapat Utama', 'lantai' => '1']);
        $r201 = Ruangan::firstOrCreate(['kode' => 'RNG-201'], ['lokasi_id' => $gedungA->id, 'nama' => 'Ruang TI', 'lantai' => '2']);

        // Barang
        $qrService = app(QrCodeService::class);
        $items = [
            ['nama' => 'Laptop Lenovo ThinkPad X1', 'kategori_id' => $elektronik->id, 'merek' => 'Lenovo', 'nomor_seri' => 'LN-X1-0099', 'tahun_perolehan' => 2024, 'nilai_perolehan' => 22000000, 'ruangan_id' => $r201->id],
            ['nama' => 'Proyektor Epson EB-X06', 'kategori_id' => $elektronik->id, 'merek' => 'Epson', 'nomor_seri' => 'EP-X06-0042', 'tahun_perolehan' => 2023, 'nilai_perolehan' => 6500000, 'ruangan_id' => $r101->id],
            ['nama' => 'Kamera DSLR Canon EOS 90D', 'kategori_id' => $elektronik->id, 'merek' => 'Canon', 'nomor_seri' => 'CN-90D-0007', 'tahun_perolehan' => 2024, 'nilai_perolehan' => 18000000, 'ruangan_id' => $r201->id],
            ['nama' => 'Kursi Rapat Lipat', 'kategori_id' => $mebel->id, 'merek' => 'Chitose', 'nomor_seri' => null, 'tahun_perolehan' => 2022, 'nilai_perolehan' => 750000, 'ruangan_id' => $r101->id],
            ['nama' => 'Sound System Portable', 'kategori_id' => $elektronik->id, 'merek' => 'JBL', 'nomor_seri' => 'JBL-PS-0011', 'tahun_perolehan' => 2023, 'nilai_perolehan' => 4200000, 'ruangan_id' => $r101->id],
        ];

        foreach ($items as $data) {
            $data['kode_barang'] = Barang::generateKode();
            $data['lokasi_id'] = $gedungA->id;
            $data['kondisi'] = 'baik';
            $data['status'] = 'tersedia';
            $barang = Barang::create($data);
            $qrService->generateForBarang($barang);
        }
    }
}
