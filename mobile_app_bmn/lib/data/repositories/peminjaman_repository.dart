import 'package:dio/dio.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../core/constants/api_constants.dart';
import '../models/peminjaman_model.dart';
import '../models/barang_model.dart';
import 'api_client.dart';

/// Peminjaman repository provider
final peminjamanRepositoryProvider = Provider<PeminjamanRepository>((ref) {
  return PeminjamanRepository(ref.read(dioProvider));
});

/// Peminjaman repository - matches Laravel PeminjamanController
class PeminjamanRepository {
  final Dio _dio;

  PeminjamanRepository(this._dio);

  /// Get all peminjaman with pagination - matches Laravel PeminjamanController::index
  Future<ApiResponse<PaginatedResponse<Peminjaman>>> getPeminjaman({
    int page = 1,
    int perPage = 15,
    String? status,
    int? peminjamId,
    String? search,
  }) async {
    try {
      final queryParams = <String, dynamic>{
        'page': page,
        'per_page': perPage,
      };
      if (status != null) queryParams['status'] = status;
      if (peminjamId != null) queryParams['peminjam_id'] = peminjamId;
      if (search != null && search.isNotEmpty) queryParams['search'] = search;

      final response = await _dio.get(
        ApiConstants.peminjaman,
        queryParameters: queryParams,
      );

      if (response.data['success'] == true) {
        final responseData = response.data['data'];
        List<dynamic> items;

        if (responseData is List) {
          items = responseData;
        } else if (responseData is Map && responseData['data'] != null) {
          items = responseData['data'] as List;
        } else {
          return ApiResponse(
            success: false,
            message: 'Format response tidak valid',
          );
        }

        final peminjamanList = items
            .map((e) => Peminjaman.fromJson(e as Map<String, dynamic>))
            .toList();

        final meta = response.data['meta'] as Map<String, dynamic>?;
        final currentPage = meta?['current_page'] as int? ?? 1;
        final lastPage = meta?['last_page'] as int? ?? 1;
        final total = meta?['total'] as int? ?? peminjamanList.length;
        final perPageMeta = meta?['per_page'] as int? ?? perPage;

        return ApiResponse(
          success: true,
          data: PaginatedResponse<Peminjaman>(
            data: peminjamanList,
            currentPage: currentPage,
            lastPage: lastPage,
            total: total,
            perPage: perPageMeta,
          ),
          message: response.data['message'] as String?,
        );
      }

      return ApiResponse(
        success: false,
        message: response.data['message'] as String? ?? 'Gagal mengambil data',
      );
    } on DioException catch (e) {
      return ApiResponse(
        success: false,
        message: 'Error: ${e.message}',
      );
    }
  }

  /// Get peminjaman by ID - matches Laravel PeminjamanController::show
  Future<ApiResponse<Peminjaman>> getPeminjamanById(int id) async {
    try {
      final response = await _dio.get('${ApiConstants.peminjaman}/$id');

      if (response.data['success'] == true) {
        final peminjaman = Peminjaman.fromJson(response.data['data'] as Map<String, dynamic>);
        return ApiResponse(
          success: true,
          data: peminjaman,
        );
      }

      return ApiResponse(
        success: false,
        message: response.data['message'] as String? ?? 'Peminjaman tidak ditemukan',
      );
    } on DioException catch (e) {
      return ApiResponse(
        success: false,
        message: 'Error: ${e.message}',
      );
    }
  }

  /// Create peminjaman - matches Laravel PeminjamanController::store
  /// For mobile app, this creates a draft peminjaman
  Future<ApiResponse<Peminjaman>> createPeminjaman({
    required int peminjamId,
    required DateTime tanggalPinjam,
    required DateTime tanggalKembaliRencana,
    required String tujuan,
    String? keperluan,
    required List<int> barangIds,
  }) async {
    try {
      final response = await _dio.post(
        ApiConstants.peminjaman,
        data: {
          'peminjam_id': peminjamId,
          'tanggal_pinjam': tanggalPinjam.toIso8601String().split('T')[0],
          'tanggal_kembali_rencana': tanggalKembaliRencana.toIso8601String().split('T')[0],
          'tujuan': tujuan,
          if (keperluan != null) 'keperluan': keperluan,
          'details': barangIds.map((id) => {'barang_id': id, 'jumlah': 1}).toList(),
        },
      );

      if (response.data['success'] == true) {
        final peminjaman = Peminjaman.fromJson(response.data['data'] as Map<String, dynamic>);
        return ApiResponse(
          success: true,
          data: peminjaman,
          message: response.data['message'] as String? ?? 'Peminjaman berhasil dibuat',
        );
      }

      return ApiResponse(
        success: false,
        message: response.data['message'] as String? ?? 'Gagal membuat peminjaman',
      );
    } on DioException catch (e) {
      return ApiResponse(
        success: false,
        message: 'Error: ${e.message}',
      );
    }
  }

  /// Submit peminjaman (draft -> menunggu_persetujuan) - matches Laravel PeminjamanController::submit
  Future<ApiResponse<Peminjaman>> submitPeminjaman(int id) async {
    try {
      final url = ApiConstants.peminjamanSubmit.replaceAll('{id}', id.toString());
      final response = await _dio.post(url);

      if (response.data['success'] == true) {
        final peminjaman = Peminjaman.fromJson(response.data['data'] as Map<String, dynamic>);
        return ApiResponse(
          success: true,
          data: peminjaman,
          message: response.data['message'] as String? ?? 'Peminjaman berhasil diajukan',
        );
      }

      return ApiResponse(
        success: false,
        message: response.data['message'] as String? ?? 'Gagal mengajukan peminjaman',
      );
    } on DioException catch (e) {
      return ApiResponse(
        success: false,
        message: 'Error: ${e.message}',
      );
    }
  }

  /// Approve peminjaman (atasan) - matches Laravel PeminjamanController::approve
  Future<ApiResponse<Peminjaman>> approvePeminjaman({
    required int id,
    String? catatanAdmin,
  }) async {
    try {
      final url = ApiConstants.peminjamanApprove.replaceAll('{id}', id.toString());
      final response = await _dio.put(url, data: {
        if (catatanAdmin != null) 'catatan_admin': catatanAdmin,
      });

      if (response.data['success'] == true) {
        final peminjaman = Peminjaman.fromJson(response.data['data'] as Map<String, dynamic>);
        return ApiResponse(
          success: true,
          data: peminjaman,
          message: response.data['message'] as String? ?? 'Peminjaman disetujui',
        );
      }

      return ApiResponse(
        success: false,
        message: response.data['message'] as String? ?? 'Gagal menyetujui peminjaman',
      );
    } on DioException catch (e) {
      return ApiResponse(
        success: false,
        message: 'Error: ${e.message}',
      );
    }
  }

  /// Approve by staff BMN - matches Laravel PeminjamanController::approveStaff
  Future<ApiResponse<Peminjaman>> approveStaffPeminjaman({
    required int id,
  }) async {
    try {
      final url = ApiConstants.peminjamanApproveStaff.replaceAll('{id}', id.toString());
      final response = await _dio.put(url);

      if (response.data['success'] == true) {
        final peminjaman = Peminjaman.fromJson(response.data['data'] as Map<String, dynamic>);
        return ApiResponse(
          success: true,
          data: peminjaman,
          message: response.data['message'] as String? ?? 'Peminjaman dikonfirmasi',
        );
      }

      return ApiResponse(
        success: false,
        message: response.data['message'] as String? ?? 'Gagal konfirmasi staff',
      );
    } on DioException catch (e) {
      return ApiResponse(
        success: false,
        message: 'Error: ${e.message}',
      );
    }
  }

  /// Reject peminjaman - matches Laravel PeminjamanController::reject
  Future<ApiResponse<Peminjaman>> rejectPeminjaman({
    required int id,
    required String alasanPenolakan,
  }) async {
    try {
      final url = ApiConstants.peminjamanReject.replaceAll('{id}', id.toString());
      final response = await _dio.put(url, data: {
        'alasan_penolakan': alasanPenolakan,
      });

      if (response.data['success'] == true) {
        final peminjaman = Peminjaman.fromJson(response.data['data'] as Map<String, dynamic>);
        return ApiResponse(
          success: true,
          data: peminjaman,
          message: response.data['message'] as String? ?? 'Peminjaman ditolak',
        );
      }

      return ApiResponse(
        success: false,
        message: response.data['message'] as String? ?? 'Gagal menolak peminjaman',
      );
    } on DioException catch (e) {
      return ApiResponse(
        success: false,
        message: 'Error: ${e.message}',
      );
    }
  }

  /// Serah terima (disetujui -> dipinjam) - matches Laravel PeminjamanController::serahTerima
  Future<ApiResponse<Peminjaman>> serahTerima(int id) async {
    try {
      final url = ApiConstants.peminjamanSerahTerima.replaceAll('{id}', id.toString());
      final response = await _dio.post(url);

      if (response.data['success'] == true) {
        final peminjaman = Peminjaman.fromJson(response.data['data'] as Map<String, dynamic>);
        return ApiResponse(
          success: true,
          data: peminjaman,
          message: response.data['message'] as String? ?? 'Barang berhasil diserahterimakan',
        );
      }

      return ApiResponse(
        success: false,
        message: response.data['message'] as String? ?? 'Gagal serah terima',
      );
    } on DioException catch (e) {
      return ApiResponse(
        success: false,
        message: 'Error: ${e.message}',
      );
    }
  }

  /// Cancel peminjaman - matches Laravel PeminjamanController::cancel
  Future<ApiResponse<Peminjaman>> cancelPeminjaman(int id) async {
    try {
      final url = ApiConstants.peminjamanCancel.replaceAll('{id}', id.toString());
      final response = await _dio.post(url);

      if (response.data['success'] == true) {
        final peminjaman = Peminjaman.fromJson(response.data['data'] as Map<String, dynamic>);
        return ApiResponse(
          success: true,
          data: peminjaman,
          message: response.data['message'] as String? ?? 'Peminjaman dibatalkan',
        );
      }

      return ApiResponse(
        success: false,
        message: response.data['message'] as String? ?? 'Gagal membatalkan',
      );
    } on DioException catch (e) {
      return ApiResponse(
        success: false,
        message: 'Error: ${e.message}',
      );
    }
  }

  /// Get available barang for selection
  Future<ApiResponse<List<Barang>>> getAvailableBarang({
    String? search,
    String? kategoriId,
    String? lokasiId,
  }) async {
    try {
      final queryParams = <String, dynamic>{
        'status': 'tersedia',
      };
      if (search != null && search.isNotEmpty) queryParams['search'] = search;
      if (kategoriId != null) queryParams['kategori_id'] = kategoriId;
      if (lokasiId != null) queryParams['lokasi_id'] = lokasiId;

      final response = await _dio.get(
        ApiConstants.barang,
        queryParameters: queryParams,
      );

      if (response.data['success'] == true) {
        List<Barang> list = [];
        final data = response.data['data'];
        if (data is List) {
          list = data.map((e) => Barang.fromJson(e as Map<String, dynamic>)).toList();
        } else if (data is Map && data['data'] != null) {
          list = (data['data'] as List)
              .map((e) => Barang.fromJson(e as Map<String, dynamic>))
              .toList();
        }
        return ApiResponse(
          success: true,
          data: list,
        );
      }

      return ApiResponse(
        success: false,
        message: response.data['message'] as String? ?? 'Gagal mengambil data',
      );
    } on DioException catch (e) {
      return ApiResponse(
        success: false,
        message: 'Error: ${e.message}',
      );
    }
  }
}

/// Pengembalian repository provider
final pengembalianRepositoryProvider = Provider<PengembalianRepository>((ref) {
  return PengembalianRepository(ref.read(dioProvider));
});

/// Pengembalian repository - matches Laravel PengembalianController
class PengembalianRepository {
  final Dio _dio;

  PengembalianRepository(this._dio);

  /// Get pengembalian by peminjaman ID
  Future<ApiResponse<Pengembalian>> getPengembalianByPeminjaman(int peminjamanId) async {
    try {
      final url = ApiConstants.pengembalianByPeminjaman.replaceAll('{id}', peminjamanId.toString());
      final response = await _dio.get(url);

      if (response.data['success'] == true) {
        final pengembalian = Pengembalian.fromJson(response.data['data'] as Map<String, dynamic>);
        return ApiResponse(
          success: true,
          data: pengembalian,
        );
      }

      return ApiResponse(
        success: false,
        message: response.data['message'] as String? ?? 'Pengembalian tidak ditemukan',
      );
    } on DioException catch (e) {
      return ApiResponse(
        success: false,
        message: 'Error: ${e.message}',
      );
    }
  }

  /// Create pengembalian (user)
  Future<ApiResponse<void>> createPengembalian({
    required int peminjamanId,
    required String kondisiBarang,
    String? catatan,
  }) async {
    try {
      final response = await _dio.post(
        ApiConstants.pengembalian,
        data: {
          'peminjaman_id': peminjamanId,
          'kondisi_barang': kondisiBarang,
          if (catatan != null) 'catatan': catatan,
        },
      );

      return ApiResponse(
        success: response.data['success'] == true,
        message: response.data['message'] as String? ?? 'Pengembalian berhasil diajukan',
      );
    } on DioException catch (e) {
      return ApiResponse(
        success: false,
        message: 'Error: ${e.message}',
      );
    }
  }
}
