/// API Constants for BMN Mobile App - matches Laravel API
class ApiConstants {
  ApiConstants._();

  /// Base URL for the API
  /// Change this to your Laravel API URL
  /// For Android emulator, use 10.0.2.2 instead of localhost
  static const String baseUrl = 'http://10.0.2.2:8000/api';

  // ==================== AUTH ====================
  static const String login = '/login';
  static const String logout = '/auth/logout';
  static const String userProfile = '/user';

  // ==================== BARANG ====================
  static const String barang = '/barang';
  static const String barangScan = '/barang/scan/{kode}';
  static const String barangByKode = '/barang/kode/{kode}';
  static const String barangHistori = '/barang/{id}/histori';
  static const String barangQr = '/barang/{id}/qr';

  // ==================== PEMINJAMAN ====================
  static const String peminjaman = '/peminjaman';
  static const String peminjamanSubmit = '/peminjaman/{id}/submit';
  static const String peminjamanApprove = '/peminjaman/{id}/approve';
  static const String peminjamanApproveStaff = '/peminjaman/{id}/approve-staff';
  static const String peminjamanReject = '/peminjaman/{id}/reject';
  static const String peminjamanSerahTerima = '/peminjaman/{id}/serah-terima';
  static const String peminjamanCancel = '/peminjaman/{id}/cancel';
  static const String peminjamanDetails = '/peminjaman/{id}/details';

  // ==================== PENGEMBALIAN ====================
  static const String pengembalian = '/pengembalian';
  static const String pengembalianByPeminjaman = '/peminjaman/{id}/pengembalian';

  // ==================== MASTER DATA ====================
  static const String kategoriBarang = '/kategori-barang';
  static const String lokasi = '/lokasi';
  static const String lokasiDirektorat = '/lokasi/direktorat';
  static const String lokasiGedung = '/lokasi/gedung';
  static const String lokasiLantai = '/lokasi/lantai';
  static const String lokasiRuangan = '/lokasi/ruangan';
  static const String lokasiUnitKerja = '/lokasi/unit-kerja';
  static const String unitKerja = '/unit-kerja';

  // ==================== DASHBOARD ====================
  static const String dashboardStats = '/dashboard/stats';
  static const String dashboardRecentActivity = '/dashboard/recent-activity';
  static const String auditLogs = '/audit-logs';

  // ==================== USER MANAGEMENT ====================
  static const String users = '/users';
  static const String usersRole = '/users/{id}/role';

  // ==================== STATUS VALUES ====================
  /// Status barang
  static const List<String> statusBarangValues = [
    'tersedia',
    'dipinjam',
    'dalam_perawatan',
    'rusak_ringan',
    'rusak_berat',
    'hilang',
    'dihapuskan',
    'dimusnahkan',
  ];

  /// Kondisi barang
  static const List<String> kondisiBarangValues = [
    'baik',
    'rusak_ringan',
    'rusak_berat',
  ];

  /// Status peminjaman
  static const List<String> statusPeminjamanValues = [
    'draft',
    'menunggu_persetujuan',
    'disetujui',
    'dipinjam',
    'dikembalikan',
    'ditolak',
    'dibatalkan',
  ];
}
