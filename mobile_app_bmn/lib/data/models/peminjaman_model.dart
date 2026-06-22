import 'package:equatable/equatable.dart';
import 'barang_model.dart';
import 'user_model.dart';

/// Status peminjaman constants
class PeminjamanStatus {
  static const String draft = 'draft';
  static const String menungguPersetujuan = 'menunggu_persetujuan';
  static const String disetujui = 'disetujui';
  static const String dipinjam = 'dipinjam';
  static const String dikembalikan = 'dikembalikan';
  static const String ditolak = 'ditolak';
  static const String dibatalkan = 'dibatalkan';

  static String getLabel(String? status) {
    switch (status) {
      case draft:
        return 'Draft';
      case menungguPersetujuan:
        return 'Menunggu Persetujuan';
      case disetujui:
        return 'Disetujui';
      case dipinjam:
        return 'Dipinjam';
      case dikembalikan:
        return 'Dikembalikan';
      case ditolak:
        return 'Ditolak';
      case dibatalkan:
        return 'Dibatalkan';
      default:
        return status ?? '-';
    }
  }
}

/// Detail Peminjaman (barang yang dipinjam)
class DetailPeminjaman extends Equatable {
  final int id;
  final int peminjamanId;
  final int barangId;
  final int jumlah;
  final Barang? barang;
  final String? kondisiPinjam;
  final String? kondisiKembali;

  const DetailPeminjaman({
    required this.id,
    required this.peminjamanId,
    required this.barangId,
    required this.jumlah,
    this.barang,
    this.kondisiPinjam,
    this.kondisiKembali,
  });

  factory DetailPeminjaman.fromJson(Map<String, dynamic> json) {
    return DetailPeminjaman(
      id: json['id'] as int,
      peminjamanId: json['peminjaman_id'] as int,
      barangId: json['barang_id'] as int,
      jumlah: json['jumlah'] as int? ?? 1,
      barang: json['barang'] != null
          ? Barang.fromJson(json['barang'] as Map<String, dynamic>)
          : null,
      kondisiPinjam: json['kondisi_pinjam'] as String?,
      kondisiKembali: json['kondisi_kembali'] as String?,
    );
  }

  @override
  List<Object?> get props => [id, peminjamanId, barangId, jumlah];
}

/// Peminjaman (Loan) model - matches Laravel PeminjamanResource
class Peminjaman extends Equatable {
  final int id;
  final String nomorPeminjaman;
  final int peminjamId;
  final String? peminjamName;
  final User? peminjam;
  final int? unitKerjaId;
  final DateTime tanggalPinjam;
  final DateTime? tanggalKembali;
  final DateTime? tanggalKembaliRencana;
  final String tujuan;
  final String? keperluan;
  final String status;
  final String? alasanPenolakan;
  final int? approvedAtasanId;
  final int? approvedPetugasId;
  final String? dokumenAtasan;
  final String? dokumenPetugas;
  final List<DetailPeminjaman>? details;
  final DateTime? createdAt;
  final DateTime? updatedAt;

  const Peminjaman({
    required this.id,
    required this.nomorPeminjaman,
    required this.peminjamId,
    this.peminjamName,
    this.peminjam,
    this.unitKerjaId,
    required this.tanggalPinjam,
    this.tanggalKembali,
    this.tanggalKembaliRencana,
    required this.tujuan,
    this.keperluan,
    required this.status,
    this.alasanPenolakan,
    this.approvedAtasanId,
    this.approvedPetugasId,
    this.dokumenAtasan,
    this.dokumenPetugas,
    this.details,
    this.createdAt,
    this.updatedAt,
  });

  factory Peminjaman.fromJson(Map<String, dynamic> json) {
    return Peminjaman(
      id: json['id'] as int,
      nomorPeminjaman: json['nomor_peminjaman'] as String,
      peminjamId: json['peminjam_id'] as int,
      peminjamName: json['peminjam_name'] as String? ??
          (json['peminjam'] != null
              ? (json['peminjam']['name'] as String?)
              : null),
      peminjam: json['peminjam'] != null
          ? User.fromJson(json['peminjam'] as Map<String, dynamic>)
          : null,
      unitKerjaId: json['unit_kerja_id'] as int?,
      tanggalPinjam: DateTime.parse(json['tanggal_pinjam'] as String),
      tanggalKembali: json['tanggal_kembali'] != null
          ? DateTime.parse(json['tanggal_kembali'] as String)
          : null,
      tanggalKembaliRencana: json['tanggal_kembali_rencana'] != null
          ? DateTime.parse(json['tanggal_kembali_rencana'] as String)
          : null,
      tujuan: json['tujuan'] as String,
      keperluan: json['keperluan'] as String?,
      status: json['status'] as String,
      alasanPenolakan: json['alasan_penolakan'] as String?,
      approvedAtasanId: json['approved_atasan_id'] as int?,
      approvedPetugasId: json['approved_petugas_id'] as int?,
      dokumenAtasan: json['dokumen_atasan'] as String?,
      dokumenPetugas: json['dokumen_petugas'] as String?,
      details: json['details'] != null
          ? (json['details'] as List)
              .map((e) => DetailPeminjaman.fromJson(e as Map<String, dynamic>))
              .toList()
          : json['details'] != null
              ? (json['details'] as List)
                  .map((e) => DetailPeminjaman.fromJson(e as Map<String, dynamic>))
                  .toList()
              : null,
      createdAt: json['created_at'] != null
          ? DateTime.parse(json['created_at'] as String)
          : null,
      updatedAt: json['updated_at'] != null
          ? DateTime.parse(json['updated_at'] as String)
          : null,
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'nomor_peminjaman': nomorPeminjaman,
      'peminjam_id': peminjamId,
      'unit_kerja_id': unitKerjaId,
      'tanggal_pinjam': tanggalPinjam.toIso8601String(),
      'tanggal_kembali': tanggalKembali?.toIso8601String(),
      'tanggal_kembali_rencana': tanggalKembaliRencana?.toIso8601String(),
      'tujuan': tujuan,
      'keperluan': keperluan,
      'status': status,
      'alasan_penolakan': alasanPenolakan,
      'created_at': createdAt?.toIso8601String(),
      'updated_at': updatedAt?.toIso8601String(),
    };
  }

  Peminjaman copyWith({
    int? id,
    String? nomorPeminjaman,
    int? peminjamId,
    String? peminjamName,
    User? peminjam,
    int? unitKerjaId,
    DateTime? tanggalPinjam,
    DateTime? tanggalKembali,
    DateTime? tanggalKembaliRencana,
    String? tujuan,
    String? keperluan,
    String? status,
    String? alasanPenolakan,
    int? approvedAtasanId,
    int? approvedPetugasId,
    String? dokumenAtasan,
    String? dokumenPetugas,
    List<DetailPeminjaman>? details,
    DateTime? createdAt,
    DateTime? updatedAt,
  }) {
    return Peminjaman(
      id: id ?? this.id,
      nomorPeminjaman: nomorPeminjaman ?? this.nomorPeminjaman,
      peminjamId: peminjamId ?? this.peminjamId,
      peminjamName: peminjamName ?? this.peminjamName,
      peminjam: peminjam ?? this.peminjam,
      unitKerjaId: unitKerjaId ?? this.unitKerjaId,
      tanggalPinjam: tanggalPinjam ?? this.tanggalPinjam,
      tanggalKembali: tanggalKembali ?? this.tanggalKembali,
      tanggalKembaliRencana: tanggalKembaliRencana ?? this.tanggalKembaliRencana,
      tujuan: tujuan ?? this.tujuan,
      keperluan: keperluan ?? this.keperluan,
      status: status ?? this.status,
      alasanPenolakan: alasanPenolakan ?? this.alasanPenolakan,
      approvedAtasanId: approvedAtasanId ?? this.approvedAtasanId,
      approvedPetugasId: approvedPetugasId ?? this.approvedPetugasId,
      dokumenAtasan: dokumenAtasan ?? this.dokumenAtasan,
      dokumenPetugas: dokumenPetugas ?? this.dokumenPetugas,
      details: details ?? this.details,
      createdAt: createdAt ?? this.createdAt,
      updatedAt: updatedAt ?? this.updatedAt,
    );
  }

  // Helper getters
  String get displayStatus => PeminjamanStatus.getLabel(status);
  int get jumlahBarang => details?.length ?? 0;
  List<Barang>? get items =>
      details?.map((d) => d.barang!).whereType<Barang>().toList() ?? [];

  bool get isDraft => status == 'draft';
  bool get isMenunggu => status == 'menunggu_persetujuan';
  bool get isDisetujui => status == 'disetujui';
  bool get isDipinjam => status == 'dipinjam';
  bool get isDikembalikan => status == 'dikembalikan';
  bool get isDitolak => status == 'ditolak';
  bool get canSubmit => status == 'draft';
  bool get canApprove => status == 'menunggu_persetujuan';
  bool get canSerahTerima => status == 'disetujui';
  bool get canReturn =>
      status == 'dipinjam' || status == 'disetujui' || status == 'ditolak';

  @override
  List<Object?> get props => [
        id,
        nomorPeminjaman,
        peminjamId,
        tanggalPinjam,
        tanggalKembali,
        status,
      ];
}

/// Pengembalian model
class Pengembalian extends Equatable {
  final int id;
  final int peminjamanId;
  final DateTime tanggalPengembalian;
  final String? kondisiBarang;
  final String? catatan;
  final String? foto;
  final DateTime? createdAt;

  const Pengembalian({
    required this.id,
    required this.peminjamanId,
    required this.tanggalPengembalian,
    this.kondisiBarang,
    this.catatan,
    this.foto,
    this.createdAt,
  });

  factory Pengembalian.fromJson(Map<String, dynamic> json) {
    return Pengembalian(
      id: json['id'] as int,
      peminjamanId: json['peminjaman_id'] as int,
      tanggalPengembalian: DateTime.parse(json['tanggal_pengembalian'] as String),
      kondisiBarang: json['kondisi_barang'] as String?,
      catatan: json['catatan'] as String?,
      foto: json['foto'] as String?,
      createdAt: json['created_at'] != null
          ? DateTime.parse(json['created_at'] as String)
          : null,
    );
  }

  @override
  List<Object?> get props => [id, peminjamanId, tanggalPengembalian];
}
