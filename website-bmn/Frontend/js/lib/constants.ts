// Constants for BMN App

export const BARANG_STATUS = {
  tersedia: 'Tersedia',
  dipinjam: 'Dipinjam',
  perbaikan: 'Dalam Perbaikan',
  hilang: 'Hilang',
  dihapuskan: 'Dihapuskan',
} as const;

export const BARANG_KONDISI = {
  baik: 'Baik',
  rusak_ringan: 'Rusak Ringan',
  rusak_berat: 'Rusak Berat',
} as const;

export const PEMINJAMAN_STATUS = {
  draft: 'Draft',
  menunggu_persetujuan: 'Menunggu Persetujuan',
  disetujui: 'Disetujui',
  ditolak: 'Ditolak',
  dipinjam: 'Dipinjam',
  dikembalikan: 'Dikembalikan',
  selesai: 'Selesai',
} as const;

export const PEMINJAMAN_STATUS_COLORS = {
  draft: 'secondary',
  menunggu_persetujuan: 'warning',
  disetujui: 'success',
  ditolak: 'destructive',
  dipinjam: 'info',
  dikembalikan: 'info',
  selesai: 'default',
} as const;

export const ROLE_LABELS = {
  super_admin: 'Super Admin',
  staff: 'Staff BMN',
  user: 'Peminjam',
} as const;
