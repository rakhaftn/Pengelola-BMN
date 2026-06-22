// Types for BMN App

export interface User {
  id: number;
  name: string;
  email: string;
  nip?: string;
  unit_kerja_id?: number;
  unit_kerja?: UnitKerja;
  jabatan?: string;
  is_active: boolean;
  roles?: string[];
  created_at?: string;
  updated_at?: string;
}

export interface UnitKerja {
  id: number;
  kode: string;
  nama: string;
  kepala?: string;
  keterangan?: string;
  is_active: boolean;
}

export interface KategoriBarang {
  id: number;
  kode: string;
  nama: string;
  keterangan?: string;
  is_active: boolean;
}

export interface Lokasi {
  id: number;
  kode: string;
  nama: string;
  lantai_id?: number;
  lantai?: Lantai;
  alamat?: string;
  keterangan?: string;
  is_active: boolean;
}

export interface Ruangan {
  id: number;
  kode: string;
  nama: string;
  lokasi_id: number;
  lokasi?: Lokasi;
  lantai?: number;
  keterangan?: string;
  is_active: boolean;
}

export interface Barang {
  id: number;
  kode_barang: string;
  nama: string;
  kategori_id: number;
  kategori?: KategoriBarang;
  lokasi_id?: number;
  ruangan_id?: number;
  ruangan?: Ruangan;
  lokasi?: Lokasi;
  merek?: string;
  nomor_seri?: string;
  tahun_perolehan?: number;
  nilai_perolehan?: number;
  kondisi: 'baik' | 'rusak_ringan' | 'rusak_berat';
  status: 'tersedia' | 'dipinjam' | 'perbaikan' | 'hilang' | 'dihapuskan';
  keterangan?: string;
  foto?: string;
  created_at?: string;
  updated_at?: string;
}

export interface Peminjaman {
  id: number;
  nomor_peminjaman: string;
  peminjam_id: number;
  peminjam?: User;
  unit_kerja_id: number;
  unit_kerja?: UnitKerja;
  status: 'draft' | 'menunggu_persetujuan' | 'disetujui' | 'ditolak' | 'dipinjam' | 'dikembalikan' | 'selesai';
  tanggal_pinjam?: string;
  tanggal_kembali_rencana?: string;
  tanggal_kembali_aktual?: string;
  tujuan?: string;
  keperluan?: string;
  approved_atasan_id?: number;
  approved_atasan_at?: string;
  approved_petugas_id?: number;
  approved_petugas_at?: string;
  rejected_by?: number;
  rejected_at?: string;
  alasan_penolakan?: string;
  catatan?: string;
  details?: DetailPeminjaman[];
  created_at?: string;
  updated_at?: string;
}

export interface DetailPeminjaman {
  id: number;
  peminjaman_id: number;
  barang_id: number;
  barang?: Barang;
  kondisi_pinjam?: string;
  kondisi_kembali?: string;
  catatan?: string;
}

export interface Pengembalian {
  id: number;
  nomor_pengembalian: string;
  peminjaman_id: number;
  peminjaman?: Peminjaman;
  diterima_oleh: number;
  penerima?: User;
  tanggal_pengembalian: string;
  kondisi_barang?: string;
  ada_kerusakan: boolean;
  catatan?: string;
  created_at?: string;
  updated_at?: string;
}

export interface DashboardStats {
  total_barang: number;
  barang_tersedia: number;
  barang_rusak: number;
  peminjaman_dipinjam: number;
  peminjaman_menunggu: number;
  peminjaman_terlambat: number;
  total_users: number;
}

export interface AuditLog {
  id: number;
  user_id: number;
  user?: User;
  event: string;
  auditable_type: string;
  auditable_id: number;
  description: string;
  old_values?: Record<string, any>;
  new_values?: Record<string, any>;
  ip_address?: string;
  user_agent?: string;
  created_at: string;
}

export interface PaginatedResponse<T> {
  data: T[];
  meta: {
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
  };
}

export interface ApiResponse<T> {
  data: T;
  message?: string;
}
