import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import '../../data/repositories/auth_repository.dart';
import '../providers/auth_provider.dart';
import '../screens/auth/login_screen.dart';
import '../screens/shell/admin_main_shell.dart';
import '../screens/shell/user_main_shell.dart';

/// App router provider
final routerProvider = Provider<GoRouter>((ref) {
  final authState = ref.watch(authProvider);

  return GoRouter(
    initialLocation: '/',
    redirect: (context, state) {
      final isAuth = authState.status == AuthStatus.authenticated;
      final isLoading = authState.status == AuthStatus.loading;
      final isInitial = authState.status == AuthStatus.initial;
      final isLoggingIn = state.matchedLocation == '/login';

      // Skip redirect while loading or initial
      if (isLoading || isInitial) return null;

      // If not authenticated, go to login
      if (!isAuth && !isLoggingIn) {
        return '/login';
      }

      // If authenticated and trying to access login, redirect to home
      if (isAuth && isLoggingIn) {
        final isAdmin = authState.user?.isAdmin ?? false;
        return isAdmin ? '/admin' : '/user';
      }

      return null;
    },
    routes: [
      // Login route
      GoRoute(
        path: '/login',
        name: 'login',
        builder: (context, state) => const LoginScreen(),
      ),
      // Admin routes
      GoRoute(
        path: '/admin',
        name: 'admin',
        builder: (context, state) => const AdminMainShell(),
      ),
      // User routes
      GoRoute(
        path: '/user',
        name: 'user',
        builder: (context, state) => const UserMainShell(),
      ),
      // Root redirect
      GoRoute(
        path: '/',
        redirect: (context, state) {
          // This will be handled by the redirect above
          return '/login';
        },
      ),
    ],
    errorBuilder: (context, state) => Scaffold(
      body: Center(
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            const Icon(Icons.error, size: 64, color: Colors.red),
            const SizedBox(height: 16),
            Text('Page not found: ${state.matchedLocation}'),
            const SizedBox(height: 16),
            ElevatedButton(
              onPressed: () => context.go('/login'),
              child: const Text('Go to Login'),
            ),
          ],
        ),
      ),
    ),
  );
});

/// Navigation helper extensions
extension NavigationExtension on BuildContext {
  void goToLogin() => go('/login');
  void goToAdmin() => go('/admin');
  void goToUser() => go('/user');
}
