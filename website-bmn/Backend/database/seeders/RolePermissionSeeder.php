<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        Artisan::call('permission:cache-reset');

        // Permissions grouped per resource (Filament-style)
        $resources = [
            'barang', 'kategori_barang', 'lokasi', 'ruangan', 'unit_kerja',
            'user', 'peminjaman', 'pengembalian', 'audit_log', 'laporan',
        ];
        $actions = ['view_any', 'view', 'create', 'update', 'delete'];

        foreach ($resources as $res) {
            foreach ($actions as $act) {
                Permission::firstOrCreate(['name' => "{$act}_{$res}", 'guard_name' => 'web']);
            }
        }

        // Workflow-specific permissions
        foreach (['approve_peminjaman', 'reject_peminjaman', 'serah_terima_peminjaman', 'proses_pengembalian', 'cetak_dokumen', 'scan_qr'] as $p) {
            Permission::firstOrCreate(['name' => $p, 'guard_name' => 'web']);
        }

        // Roles
        $superAdmin = Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);
        $staff = Role::firstOrCreate(['name' => 'staff', 'guard_name' => 'web']);
        $user = Role::firstOrCreate(['name' => 'user', 'guard_name' => 'web']);

        // Super admin: all permissions + memberikan ijin peminjaman
        $superAdmin->syncPermissions(Permission::all());

        // Staff (Petugas BMN): manage master data, peminjaman, pengembalian, dokumen, scan QR
        $staff->syncPermissions(Permission::whereIn('name', array_merge(
            $this->crud(['barang', 'kategori_barang', 'lokasi', 'ruangan', 'unit_kerja', 'peminjaman', 'pengembalian', 'laporan']),
            ['view_any_audit_log', 'view_audit_log',
             'serah_terima_peminjaman', 'proses_pengembalian', 'cetak_dokumen', 'scan_qr',
             'approve_peminjaman', 'reject_peminjaman']
        ))->get());

        // User (Peminjam): create & view own peminjaman, view barang, scan
        $user->syncPermissions(Permission::whereIn('name', [
            'view_any_peminjaman', 'view_peminjaman', 'create_peminjaman', 'update_peminjaman',
            'view_any_barang', 'view_barang', 'scan_qr',
        ])->get());
    }

    private function crud(array $resources): array
    {
        $out = [];
        foreach ($resources as $r) {
            foreach (['view_any', 'view', 'create', 'update', 'delete'] as $a) {
                $out[] = "{$a}_{$r}";
            }
        }
        return $out;
    }
}
