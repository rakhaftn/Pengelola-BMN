import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:shared_preferences/shared_preferences.dart';
import '../../core/constants/app_constants.dart';
import '../../data/repositories/auth_repository.dart';

/// Auth notifier for managing authentication state
class AuthNotifier extends StateNotifier<AuthState> {
  final AuthRepository _repository;

  AuthNotifier(this._repository) : super(const AuthState()) {
    _checkAuth();
  }

  Future<void> _checkAuth() async {
    state = state.copyWith(status: AuthStatus.loading);

    try {
      final isAuth = await _repository.isAuthenticated();
      if (isAuth) {
        final user = await _repository.getStoredUser();
        if (user != null) {
          state = AuthState(
            status: AuthStatus.authenticated,
            user: user,
          );
          return;
        }
      }
      state = const AuthState(status: AuthStatus.unauthenticated);
    } catch (e) {
      state = const AuthState(status: AuthStatus.unauthenticated);
    }
  }

  Future<bool> login({
    required String email,
    required String password,
  }) async {
    state = state.copyWith(status: AuthStatus.loading, error: null);

    final response = await _repository.login(
      email: email,
      password: password,
    );

    if (response.success && response.data != null) {
      state = AuthState(
        status: AuthStatus.authenticated,
        user: response.data,
      );
      return true;
    }

    state = state.copyWith(
      status: AuthStatus.unauthenticated,
      error: response.message ?? 'Login gagal',
    );
    return false;
  }

  Future<void> logout() async {
    await _repository.logout();
    state = const AuthState(status: AuthStatus.unauthenticated);
  }

  Future<void> refreshProfile() async {
    final response = await _repository.getProfile();
    if (response.success && response.data != null) {
      state = state.copyWith(user: response.data);
    }
  }

  void clearError() {
    state = state.copyWith(error: null);
  }
}

/// Auth state provider
final authProvider = StateNotifierProvider<AuthNotifier, AuthState>((ref) {
  return AuthNotifier(ref.read(authRepositoryProvider));
});

/// User role provider
final userRoleProvider = FutureProvider<String?>((ref) async {
  final prefs = await SharedPreferences.getInstance();
  return prefs.getString(AppConstants.roleKey);
});

/// Is admin provider
final isAdminProvider = Provider<bool>((ref) {
  final authState = ref.watch(authProvider);
  return authState.user?.isAdmin ?? false;
});

/// Is super admin provider
final isSuperAdminProvider = Provider<bool>((ref) {
  final authState = ref.watch(authProvider);
  return authState.user?.isSuperAdmin ?? false;
});
