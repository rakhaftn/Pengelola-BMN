/// App-wide constants
class AppConstants {
  AppConstants._();

  /// App Name
  static const String appName = 'BMN Mobile';

  /// App Version
  static const String appVersion = '1.0.0';

  /// Storage Keys
  static const String tokenKey = 'auth_token';
  static const String userKey = 'user_data';
  static const String roleKey = 'user_role';
  static const String themeKey = 'app_theme';

  /// Roles
  static const String roleAdmin = 'admin';
  static const String roleUser = 'user';
  static const String roleSuperAdmin = 'super_admin';

  /// Animation durations
  static const Duration shortAnimation = Duration(milliseconds: 200);
  static const Duration mediumAnimation = Duration(milliseconds: 350);
  static const Duration longAnimation = Duration(milliseconds: 500);

  /// Pagination
  static const int defaultPageSize = 20;

  /// Date formats
  static const String dateFormat = 'dd MMMM yyyy';
  static const String dateTimeFormat = 'dd MMMM yyyy HH:mm';
  static const String timeFormat = 'HH:mm';
}
