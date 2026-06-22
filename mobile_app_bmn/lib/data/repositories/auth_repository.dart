import 'dart:convert';
import 'package:dio/dio.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:shared_preferences/shared_preferences.dart';
import '../../core/constants/api_constants.dart';
import '../../core/constants/app_constants.dart';
import '../models/user_model.dart';
import 'api_client.dart';

/// Auth repository provider
final authRepositoryProvider = Provider<AuthRepository>((ref) {
  return AuthRepository(ref.read(dioProvider));
});

/// Auth state
enum AuthStatus { initial, authenticated, unauthenticated, loading }

class AuthState {
  final AuthStatus status;
  final User? user;
  final String? error;

  const AuthState({
    this.status = AuthStatus.initial,
    this.user,
    this.error,
  });

  AuthState copyWith({
    AuthStatus? status,
    User? user,
    String? error,
  }) {
    return AuthState(
      status: status ?? this.status,
      user: user ?? this.user,
      error: error,
    );
  }
}

/// Auth repository - matches Laravel Sanctum API
class AuthRepository {
  final Dio _dio;

  AuthRepository(this._dio);

  /// Get stored token
  Future<String?> getStoredToken() async {
    final prefs = await SharedPreferences.getInstance();
    return prefs.getString(AppConstants.tokenKey);
  }

  /// Get stored user
  Future<User?> getStoredUser() async {
    final prefs = await SharedPreferences.getInstance();
    final userJson = prefs.getString(AppConstants.userKey);
    if (userJson != null) {
      return User.fromJson(jsonDecode(userJson) as Map<String, dynamic>);
    }
    return null;
  }

  /// Check if user is authenticated
  Future<bool> isAuthenticated() async {
    final token = await getStoredToken();
    return token != null && token.isNotEmpty;
  }

  /// Login - matches Laravel AuthController::login
  Future<ApiResponse<User>> login({
    required String email,
    required String password,
  }) async {
    try {
      final response = await _dio.post(
        ApiConstants.login,
        data: {
          'email': email,
          'password': password,
        },
      );

      final data = response.data;

      if (data['success'] == true && data['data'] != null) {
        final tokenData = data['data'];
        final token = tokenData['token'] as String;
        final userData = tokenData['user'] as Map<String, dynamic>;
        final user = User.fromJson(userData);

        // Store token and user
        final prefs = await SharedPreferences.getInstance();
        await prefs.setString(AppConstants.tokenKey, token);
        await prefs.setString(AppConstants.userKey, jsonEncode(userData));

        return ApiResponse(
          success: true,
          data: user,
          message: data['message'] as String? ?? 'Login berhasil',
        );
      }

      return ApiResponse(
        success: false,
        message: data['message'] as String? ?? 'Login gagal',
      );
    } on DioException catch (e) {
      return ApiResponse(
        success: false,
        message: _handleDioError(e),
      );
    } catch (e) {
      return ApiResponse(
        success: false,
        message: 'Terjadi kesalahan: ${e.toString()}',
      );
    }
  }

  /// Logout
  Future<void> logout() async {
    try {
      await _dio.post(ApiConstants.logout);
    } catch (_) {
      // Ignore logout errors
    } finally {
      final prefs = await SharedPreferences.getInstance();
      await prefs.remove(AppConstants.tokenKey);
      await prefs.remove(AppConstants.userKey);
      await prefs.remove(AppConstants.roleKey);
    }
  }

  /// Get current user profile
  Future<ApiResponse<User>> getProfile() async {
    try {
      final response = await _dio.get(ApiConstants.userProfile);

      if (response.data['success'] == true) {
        final user = User.fromJson(response.data['data'] as Map<String, dynamic>);
        return ApiResponse(
          success: true,
          data: user,
        );
      }

      return ApiResponse(
        success: false,
        message: response.data['message'] as String? ?? 'Gagal mengambil profil',
      );
    } on DioException catch (e) {
      return ApiResponse(
        success: false,
        message: _handleDioError(e),
      );
    }
  }

  String _handleDioError(DioException e) {
    if (e.response != null) {
      final data = e.response!.data;
      if (data is Map && data['message'] != null) {
        return data['message'] as String;
      }
      // Handle Laravel validation errors
      if (data is Map && data['errors'] != null) {
        final errors = data['errors'] as Map<String, dynamic>;
        if (errors.isNotEmpty) {
          final firstError = errors.values.first;
          if (firstError is List && firstError.isNotEmpty) {
            return firstError.first.toString();
          }
        }
      }
      switch (e.response!.statusCode) {
        case 401:
          return 'Email atau password salah';
        case 422:
          return 'Validasi gagal';
        case 403:
          return 'Akses ditolak';
        case 404:
          return 'Data tidak ditemukan';
        default:
          return 'Server error: ${e.response!.statusCode}';
      }
    }
    if (e.type == DioExceptionType.connectionTimeout ||
        e.type == DioExceptionType.receiveTimeout) {
      return 'Koneksi timeout. Periksa koneksi internet Anda';
    }
    if (e.type == DioExceptionType.connectionError) {
      return 'Tidak dapat terhubung ke server. Pastikan server Laravel berjalan.';
    }
    return 'Terjadi kesalahan koneksi';
  }
}
