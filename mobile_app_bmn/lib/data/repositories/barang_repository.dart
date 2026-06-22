import 'package:dio/dio.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../core/constants/api_constants.dart';
import '../models/barang_model.dart';
import '../models/peminjaman_model.dart';
import 'api_client.dart';

/// Barang repository provider
final barangRepositoryProvider = Provider<BarangRepository>((ref) {
  return BarangRepository(ref.read(dioProvider));
});

/// Barang repository - matches Laravel BarangController
class BarangRepository {
  final Dio _dio;

  BarangRepository(this._dio);

  /// Get all barang with pagination - matches Laravel BarangController::index
  Future<ApiResponse<PaginatedResponse<Barang>>> getBarang({
    int page = 1,
    int perPage = 15,
    String? search,
    String? kategoriId,
    String? lokasiId,
    String? kondisi,
    String? status,
  }) async {
    try {
      final queryParams = <String, dynamic>{
        'page': page,
        'per_page': perPage,
      };
      if (search != null && search.isNotEmpty) queryParams['search'] = search;
      if (kategoriId != null) queryParams['kategori_id'] = kategoriId;
      if (lokasiId != null) queryParams['lokasi_id'] = lokasiId;
      if (kondisi != null) queryParams['kondisi'] = kondisi;
      if (status != null) queryParams['status'] = status;

      final response = await _dio.get(
        ApiConstants.barang,
        queryParameters: queryParams,
      );

      if (response.data['success'] == true) {
        // Laravel returns 'data' directly with meta for paginated
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

        final barangList = items
            .map((e) => Barang.fromJson(e as Map<String, dynamic>))
            .toList();

        // Get meta for pagination
        final meta = response.data['meta'] as Map<String, dynamic>?;
        final currentPage = meta?['current_page'] as int? ?? 1;
        final lastPage = meta?['last_page'] as int? ?? 1;
        final total = meta?['total'] as int? ?? barangList.length;
        final perPageMeta = meta?['per_page'] as int? ?? perPage;

        return ApiResponse(
          success: true,
          data: PaginatedResponse<Barang>(
            data: barangList,
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

  /// Get barang by ID - matches Laravel BarangController::show
  Future<ApiResponse<Barang>> getBarangById(int id) async {
    try {
      final response = await _dio.get('${ApiConstants.barang}/$id');

      if (response.data['success'] == true) {
        final barang = Barang.fromJson(response.data['data'] as Map<String, dynamic>);
        return ApiResponse(
          success: true,
          data: barang,
        );
      }

      return ApiResponse(
        success: false,
        message: response.data['message'] as String? ?? 'Barang tidak ditemukan',
      );
    } on DioException catch (e) {
      return ApiResponse(
        success: false,
        message: 'Error: ${e.message}',
      );
    }
  }

  /// Scan barang by QR code - matches Laravel QrCodeController::scan
  Future<ApiResponse<Barang>> scanBarang(String kodeBarang) async {
    try {
      final url = ApiConstants.barangScan.replaceAll('{kode}', kodeBarang);
      final response = await _dio.get(url);

      if (response.data['success'] == true) {
        final data = response.data['data'] as Map<String, dynamic>;
        // QrCodeController returns different format, map it
        final barang = Barang.fromJson({
          'id': 0,
          'kode_barang': data['kode_barang'],
          'nama': data['nama'],
          'merek': data['merek'],
          'kategori': {'nama': data['kategori']},
          'lokasi': data['lokasi'] != null ? {'nama': data['lokasi']} : null,
          'ruangan': data['ruangan'] != null ? {'nama': data['ruangan']} : null,
          'gedung': data['gedung'] != null ? {'nama': data['gedung']} : null,
          'kondisi': data['kondisi'],
          'status': data['status'],
        });
        return ApiResponse(
          success: true,
          data: barang,
          message: response.data['message'] as String? ?? 'Barang ditemukan',
        );
      }

      return ApiResponse(
        success: false,
        message: response.data['message'] as String? ?? 'Barang tidak ditemukan',
      );
    } on DioException catch (e) {
      if (e.response?.statusCode == 404) {
        return ApiResponse(
          success: false,
          message: 'Barang tidak ditemukan',
        );
      }
      return ApiResponse(
        success: false,
        message: 'Error: ${e.message}',
      );
    }
  }

  /// Get barang by kode_barang - matches Laravel BarangController::findByKode
  Future<ApiResponse<Barang>> getBarangByKode(String kode) async {
    try {
      final url = ApiConstants.barangByKode.replaceAll('{kode}', kode);
      final response = await _dio.get(url);

      if (response.data['success'] == true) {
        final barang = Barang.fromJson(response.data['data'] as Map<String, dynamic>);
        return ApiResponse(
          success: true,
          data: barang,
        );
      }

      return ApiResponse(
        success: false,
        message: response.data['message'] as String? ?? 'Barang tidak ditemukan',
      );
    } on DioException catch (e) {
      return ApiResponse(
        success: false,
        message: 'Error: ${e.message}',
      );
    }
  }

  /// Create barang (admin only) - matches Laravel BarangController::store
  Future<ApiResponse<Barang>> createBarang({
    required String nama,
    required int kategoriId,
    String? merek,
    String? nomorSeri,
    int? tahunPerolehan,
    double? nilaiPerolehan,
    String? kondisi,
    String? status,
    String? keterangan,
    int? lokasiId,
    int? ruanganId,
    int? gedungId,
  }) async {
    try {
      final response = await _dio.post(
        ApiConstants.barang,
        data: {
          'nama': nama,
          'kategori_id': kategoriId,
          if (merek != null) 'merek': merek,
          if (nomorSeri != null) 'nomor_seri': nomorSeri,
          if (tahunPerolehan != null) 'tahun_perolehan': tahunPerolehan,
          if (nilaiPerolehan != null) 'nilai_perolehan': nilaiPerolehan,
          if (kondisi != null) 'kondisi': kondisi,
          if (status != null) 'status': status,
          if (keterangan != null) 'keterangan': keterangan,
          if (lokasiId != null) 'lokasi_id': lokasiId,
          if (ruanganId != null) 'ruangan_id': ruanganId,
          if (gedungId != null) 'gedung_id': gedungId,
        },
      );

      if (response.data['success'] == true) {
        final barang = Barang.fromJson(response.data['data'] as Map<String, dynamic>);
        return ApiResponse(
          success: true,
          data: barang,
          message: response.data['message'] as String? ?? 'Barang berhasil ditambahkan',
        );
      }

      return ApiResponse(
        success: false,
        message: response.data['message'] as String? ?? 'Gagal menambahkan barang',
      );
    } on DioException catch (e) {
      return ApiResponse(
        success: false,
        message: 'Error: ${e.message}',
      );
    }
  }

  /// Update barang (admin only) - matches Laravel BarangController::update
  Future<ApiResponse<Barang>> updateBarang({
    required int id,
    String? nama,
    int? kategoriId,
    String? merek,
    String? nomorSeri,
    String? kondisi,
    String? status,
    String? keterangan,
  }) async {
    try {
      final response = await _dio.put(
        '${ApiConstants.barang}/$id',
        data: {
          if (nama != null) 'nama': nama,
          if (kategoriId != null) 'kategori_id': kategoriId,
          if (merek != null) 'merek': merek,
          if (nomorSeri != null) 'nomor_seri': nomorSeri,
          if (kondisi != null) 'kondisi': kondisi,
          if (status != null) 'status': status,
          if (keterangan != null) 'keterangan': keterangan,
        },
      );

      if (response.data['success'] == true) {
        final barang = Barang.fromJson(response.data['data'] as Map<String, dynamic>);
        return ApiResponse(
          success: true,
          data: barang,
          message: response.data['message'] as String? ?? 'Barang berhasil diperbarui',
        );
      }

      return ApiResponse(
        success: false,
        message: response.data['message'] as String? ?? 'Gagal memperbarui barang',
      );
    } on DioException catch (e) {
      return ApiResponse(
        success: false,
        message: 'Error: ${e.message}',
      );
    }
  }

  /// Delete barang (admin only) - matches Laravel BarangController::destroy
  Future<ApiResponse<void>> deleteBarang(int id) async {
    try {
      final response = await _dio.delete('${ApiConstants.barang}/$id');

      return ApiResponse(
        success: response.data['success'] == true,
        message: response.data['message'] as String? ?? 'Barang berhasil dihapus',
      );
    } on DioException catch (e) {
      return ApiResponse(
        success: false,
        message: 'Error: ${e.message}',
      );
    }
  }

  /// Get all kategori - matches Laravel KategoriBarangController
  Future<ApiResponse<List<Kategori>>> getKategori() async {
    try {
      final response = await _dio.get(ApiConstants.kategoriBarang);

      if (response.data['success'] == true) {
        final list = (response.data['data'] as List)
            .map((e) => Kategori.fromJson(e as Map<String, dynamic>))
            .toList();
        return ApiResponse(
          success: true,
          data: list,
        );
      }

      return ApiResponse(
        success: false,
        message: response.data['message'] as String? ?? 'Gagal mengambil kategori',
      );
    } on DioException catch (e) {
      return ApiResponse(
        success: false,
        message: 'Error: ${e.message}',
      );
    }
  }

  /// Get all lokasi/ruangan - matches Laravel LokasiController
  Future<ApiResponse<List<Lokasi>>> getLokasi() async {
    try {
      final response = await _dio.get(ApiConstants.lokasiRuangan);

      if (response.data['success'] == true) {
        final list = (response.data['data'] as List)
            .map((e) => Lokasi.fromJson(e as Map<String, dynamic>))
            .toList();
        return ApiResponse(
          success: true,
          data: list,
        );
      }

      return ApiResponse(
        success: false,
        message: response.data['message'] as String? ?? 'Gagal mengambil lokasi',
      );
    } on DioException catch (e) {
      return ApiResponse(
        success: false,
        message: 'Error: ${e.message}',
      );
    }
  }
}
