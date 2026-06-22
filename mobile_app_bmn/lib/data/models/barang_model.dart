import 'package:equatable/equatable.dart';

/// Kategori model from Laravel
class Kategori extends Equatable {
  final int id;
  final String nama;
  final String? kode;

  const Kategori({
    required this.id,
    required this.nama,
    this.kode,
  });

  factory Kategori.fromJson(Map<String, dynamic> json) {
    return Kategori(
      id: json['id'] as int,
      nama: json['nama'] as String,
      kode: json['kode'] as String?,
    );
  }

  Map<String, dynamic> toJson() {
    return {'id': id, 'nama': nama, 'kode': kode};
  }

  @override
  List<Object?> get props => [id, nama, kode];
}

/// Lokasi model from Laravel
class Lokasi extends Equatable {
  final int id;
  final String nama;

  const Lokasi({
    required this.id,
    required this.nama,
  });

  factory Lokasi.fromJson(Map<String, dynamic> json) {
    return Lokasi(
      id: json['id'] as int,
      nama: json['nama'] as String,
    );
  }

  Map<String, dynamic> toJson() {
    return {'id': id, 'nama': nama};
  }

  @override
  List<Object?> get props => [id, nama];
}

/// Ruangan model
class Ruangan extends Equatable {
  final int id;
  final String nama;

  const Ruangan({
    required this.id,
    required this.nama,
  });

  factory Ruangan.fromJson(Map<String, dynamic> json) {
    return Ruangan(
      id: json['id'] as int,
      nama: json['nama'] as String,
    );
  }

  @override
  List<Object?> get props => [id, nama];
}

/// Gedung model
class Gedung extends Equatable {
  final int id;
  final String nama;

  const Gedung({
    required this.id,
    required this.nama,
  });

  factory Gedung.fromJson(Map<String, dynamic> json) {
    return Gedung(
      id: json['id'] as int,
      nama: json['nama'] as String,
    );
  }

  @override
  List<Object?> get props => [id, nama];
}

/// Barang (Item/Asset) model - matches Laravel BarangResource
class Barang extends Equatable {
  final int id;
  final String kodeBarang;
  final String nama;
  final String? merek;
  final String? nomorSeri;
  final int? tahunPerolehan;
  final double? nilaiPerolehan;
  final String kondisi;
  final String? kondisiLabel;
  final String status;
  final String? statusLabel;
  final String? keterangan;
  final String? foto;
  final String? qrUrl;
  final Kategori? kategori;
  final Lokasi? lokasi;
  final Ruangan? ruangan;
  final Gedung? gedung;
  final DateTime? createdAt;
  final DateTime? updatedAt;

  const Barang({
    required this.id,
    required this.kodeBarang,
    required this.nama,
    this.merek,
    this.nomorSeri,
    this.tahunPerolehan,
    this.nilaiPerolehan,
    required this.kondisi,
    this.kondisiLabel,
    required this.status,
    this.statusLabel,
    this.keterangan,
    this.foto,
    this.qrUrl,
    this.kategori,
    this.lokasi,
    this.ruangan,
    this.gedung,
    this.createdAt,
    this.updatedAt,
  });

  factory Barang.fromJson(Map<String, dynamic> json) {
    return Barang(
      id: json['id'] as int,
      kodeBarang: json['kode_barang'] as String,
      nama: json['nama'] as String,
      merek: json['merek'] as String?,
      nomorSeri: json['nomor_seri'] as String?,
      tahunPerolehan: json['tahun_perolehan'] as int?,
      nilaiPerolehan: (json['nilai_perolehan'] as num?)?.toDouble(),
      kondisi: json['kondisi'] as String? ?? 'baik',
      kondisiLabel: json['kondisi_label'] as String?,
      status: json['status'] as String? ?? 'tersedia',
      statusLabel: json['status_label'] as String?,
      keterangan: json['keterangan'] as String?,
      foto: json['foto'] as String?,
      qrUrl: json['qr_url'] as String?,
      kategori: json['kategori'] != null
          ? Kategori.fromJson(json['kategori'] as Map<String, dynamic>)
          : null,
      lokasi: json['lokasi'] != null
          ? Lokasi.fromJson(json['lokasi'] as Map<String, dynamic>)
          : null,
      ruangan: json['ruangan'] != null
          ? Ruangan.fromJson(json['ruangan'] as Map<String, dynamic>)
          : null,
      gedung: json['gedung'] != null
          ? Gedung.fromJson(json['gedung'] as Map<String, dynamic>)
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
      'kode_barang': kodeBarang,
      'nama': nama,
      'merek': merek,
      'nomor_seri': nomorSeri,
      'tahun_perolehan': tahunPerolehan,
      'nilai_perolehan': nilaiPerolehan,
      'kondisi': kondisi,
      'kondisi_label': kondisiLabel,
      'status': status,
      'status_label': statusLabel,
      'keterangan': keterangan,
      'foto': foto,
      'qr_url': qrUrl,
      'created_at': createdAt?.toIso8601String(),
      'updated_at': updatedAt?.toIso8601String(),
    };
  }

  Barang copyWith({
    int? id,
    String? kodeBarang,
    String? nama,
    String? merek,
    String? nomorSeri,
    int? tahunPerolehan,
    double? nilaiPerolehan,
    String? kondisi,
    String? kondisiLabel,
    String? status,
    String? statusLabel,
    String? keterangan,
    String? foto,
    String? qrUrl,
    Kategori? kategori,
    Lokasi? lokasi,
    Ruangan? ruangan,
    Gedung? gedung,
    DateTime? createdAt,
    DateTime? updatedAt,
  }) {
    return Barang(
      id: id ?? this.id,
      kodeBarang: kodeBarang ?? this.kodeBarang,
      nama: nama ?? this.nama,
      merek: merek ?? this.merek,
      nomorSeri: nomorSeri ?? this.nomorSeri,
      tahunPerolehan: tahunPerolehan ?? this.tahunPerolehan,
      nilaiPerolehan: nilaiPerolehan ?? this.nilaiPerolehan,
      kondisi: kondisi ?? this.kondisi,
      kondisiLabel: kondisiLabel ?? this.kondisiLabel,
      status: status ?? this.status,
      statusLabel: statusLabel ?? this.statusLabel,
      keterangan: keterangan ?? this.keterangan,
      foto: foto ?? this.foto,
      qrUrl: qrUrl ?? this.qrUrl,
      kategori: kategori ?? this.kategori,
      lokasi: lokasi ?? this.lokasi,
      ruangan: ruangan ?? this.ruangan,
      gedung: gedung ?? this.gedung,
      createdAt: createdAt ?? this.createdAt,
      updatedAt: updatedAt ?? this.updatedAt,
    );
  }

  // Helper getters for backward compatibility
  String get namaBarang => nama;
  String get lokasiNama => lokasi?.nama ?? ruangan?.nama ?? '-';
  String get kategoriNama => kategori?.nama ?? '-';
  String get fotoUrl => foto ?? '';
  String get displayKondisi => kondisiLabel ?? _mapKondisiLabel(kondisi);
  String get displayStatus => statusLabel ?? _mapStatusLabel(status);

  String _mapKondisiLabel(String? kondisi) {
    switch (kondisi) {
      case 'baik':
        return 'Baik';
      case 'rusak_ringan':
        return 'Rusak Ringan';
      case 'rusak_berat':
        return 'Rusak Berat';
      default:
        return kondisi ?? '-';
    }
  }

  String _mapStatusLabel(String? status) {
    switch (status) {
      case 'pengadaan':
        return 'Pengadaan';
      case 'tersedia':
        return 'Tersedia';
      case 'dipinjam':
        return 'Dipinjam';
      case 'dalam_perawatan':
        return 'Dalam Perawatan';
      case 'rusak_ringan':
        return 'Rusak Ringan';
      case 'rusak_berat':
        return 'Rusak Berat';
      case 'hilang':
        return 'Hilang';
      case 'dihapuskan':
        return 'Dihapuskan';
      case 'dimusnahkan':
        return 'Dimusnahkan';
      default:
        return status ?? '-';
    }
  }

  bool get isAvailable => status == 'tersedia';
  bool get isDipinjam => status == 'dipinjam';
  bool get isRusak => kondisi == 'rusak_ringan' || kondisi == 'rusak_berat';

  @override
  List<Object?> get props => [
        id,
        kodeBarang,
        nama,
        kategori,
        lokasi,
        ruangan,
        gedung,
        kondisi,
        status,
      ];
}
