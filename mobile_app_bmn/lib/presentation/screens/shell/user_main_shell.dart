import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../../core/constants/app_colors.dart';
import '../../providers/auth_provider.dart';
import '../../providers/barang_provider.dart';
import '../../providers/peminjaman_provider.dart';
import '../barang/barang_list_screen.dart';
import '../peminjaman/peminjaman_list_screen.dart';
import '../peminjaman/peminjaman_form_screen.dart';
import 'admin_main_shell.dart';

/// User main shell with bottom navigation
class UserMainShell extends ConsumerStatefulWidget {
  const UserMainShell({super.key});

  @override
  ConsumerState<UserMainShell> createState() => _UserMainShellState();
}

class _UserMainShellState extends ConsumerState<UserMainShell> {
  int _currentIndex = 0;

  final _pages = const [
    UserDashboard(),
    BarangListScreen(isUserView: true),
    UserPeminjamanScreen(),
    ProfileScreen(),
  ];

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      body: IndexedStack(
        index: _currentIndex,
        children: _pages,
      ),
      bottomNavigationBar: NavigationBar(
        selectedIndex: _currentIndex,
        onDestinationSelected: (index) {
          setState(() => _currentIndex = index);
        },
        destinations: const [
          NavigationDestination(
            icon: Icon(Icons.home_outlined),
            selectedIcon: Icon(Icons.home),
            label: 'Beranda',
          ),
          NavigationDestination(
            icon: Icon(Icons.inventory_2_outlined),
            selectedIcon: Icon(Icons.inventory_2),
            label: 'Barang',
          ),
          NavigationDestination(
            icon: Icon(Icons.assignment_outlined),
            selectedIcon: Icon(Icons.assignment),
            label: 'Pinjaman Saya',
          ),
          NavigationDestination(
            icon: Icon(Icons.person_outlined),
            selectedIcon: Icon(Icons.person),
            label: 'Profil',
          ),
        ],
      ),
    );
  }
}

/// User Dashboard
class UserDashboard extends ConsumerWidget {
  const UserDashboard({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final authState = ref.watch(authProvider);
    final user = authState.user;
    final barangState = ref.watch(barangListProvider);

    // Get user peminjaman
    final userId = user?.id ?? 0;
    final userPeminjaman = ref.watch(userPeminjamanProvider(userId));
    final activeLoans = userPeminjaman.items.where((p) => p.status == 'Disetujui').toList();
    final pendingLoans = userPeminjaman.items.where((p) => p.status == 'Menunggu').toList();

    return Scaffold(
      appBar: AppBar(
        title: const Text('BMN Mobile'),
        automaticallyImplyLeading: false,
      ),
      body: RefreshIndicator(
        onRefresh: () async {
          ref.read(barangListProvider.notifier).refresh();
          ref.read(userPeminjamanProvider(userId).notifier).refresh();
        },
        child: SingleChildScrollView(
          physics: const AlwaysScrollableScrollPhysics(),
          padding: const EdgeInsets.all(16),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              // Welcome card
              Card(
                child: Padding(
                  padding: const EdgeInsets.all(16),
                  child: Row(
                    children: [
                      CircleAvatar(
                        radius: 25,
                        backgroundColor: AppColors.secondary,
                        child: Text(
                          user?.name.substring(0, 1).toUpperCase() ?? 'U',
                          style: const TextStyle(
                            color: Colors.white,
                            fontSize: 20,
                            fontWeight: FontWeight.bold,
                          ),
                        ),
                      ),
                      const SizedBox(width: 12),
                      Expanded(
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Text(
                              'Halo, ${user?.name ?? "User"}!',
                              style: Theme.of(context).textTheme.titleMedium?.copyWith(
                                    fontWeight: FontWeight.bold,
                                  ),
                            ),
                            Text(
                              user?.email ?? '',
                              style: Theme.of(context).textTheme.bodySmall?.copyWith(
                                    color: AppColors.textSecondary,
                                  ),
                            ),
                          ],
                        ),
                      ),
                    ],
                  ),
                ),
              ),
              const SizedBox(height: 20),
              // Active loans card
              if (activeLoans.isNotEmpty) ...[
                Card(
                  color: AppColors.primary.withValues(alpha: 0.1),
                  child: Padding(
                    padding: const EdgeInsets.all(16),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Row(
                          children: [
                            const Icon(Icons.pending_actions, color: AppColors.primary),
                            const SizedBox(width: 8),
                            Text(
                              'Peminjaman Aktif',
                              style: Theme.of(context).textTheme.titleSmall?.copyWith(
                                    fontWeight: FontWeight.bold,
                                    color: AppColors.primary,
                                  ),
                            ),
                          ],
                        ),
                        const SizedBox(height: 8),
                        Text(
                          'Anda memiliki ${activeLoans.length} peminjaman aktif',
                          style: Theme.of(context).textTheme.bodyMedium,
                        ),
                        const SizedBox(height: 12),
                        ...activeLoans.take(2).map((p) => Padding(
                              padding: const EdgeInsets.only(bottom: 4),
                              child: Row(
                                children: [
                                  const Icon(Icons.inventory_2, size: 16, color: AppColors.textSecondary),
                                  const SizedBox(width: 4),
                                  Text('#${p.id} - ${p.items?.length ?? 0} barang'),
                                ],
                              ),
                            )),
                        TextButton(
                          onPressed: () {
                            // Navigate to peminjaman
                          },
                          child: const Text('Lihat Selengkapnya'),
                        ),
                      ],
                    ),
                  ),
                ),
                const SizedBox(height: 16),
              ],
              // Stats
              Text(
                'Ringkasan',
                style: Theme.of(context).textTheme.titleMedium?.copyWith(
                      fontWeight: FontWeight.bold,
                    ),
              ),
              const SizedBox(height: 12),
              Row(
                children: [
                  Expanded(
                    child: _UserStatCard(
                      icon: Icons.inventory,
                      label: 'Barang Tersedia',
                      value: barangState.items.where((b) => b.status == 'Tersedia').length.toString(),
                      color: AppColors.success,
                    ),
                  ),
                  const SizedBox(width: 12),
                  Expanded(
                    child: _UserStatCard(
                      icon: Icons.pending,
                      label: 'Menunggu',
                      value: pendingLoans.length.toString(),
                      color: AppColors.warning,
                    ),
                  ),
                ],
              ),
              const SizedBox(height: 12),
              Row(
                children: [
                  Expanded(
                    child: _UserStatCard(
                      icon: Icons.check_circle,
                      label: 'Dikembalikan',
                      value: userPeminjaman.items.where((p) => p.status == 'Dikembalikan').length.toString(),
                      color: AppColors.info,
                    ),
                  ),
                  const SizedBox(width: 12),
                  Expanded(
                    child: _UserStatCard(
                      icon: Icons.cancel,
                      label: 'Ditolak',
                      value: userPeminjaman.items.where((p) => p.status == 'Ditolak').length.toString(),
                      color: AppColors.error,
                    ),
                  ),
                ],
              ),
              const SizedBox(height: 24),
              // Quick action - Ajukan Peminjaman
              SizedBox(
                width: double.infinity,
                child: ElevatedButton.icon(
                  onPressed: () {
                    Navigator.push(
                      context,
                      MaterialPageRoute(
                        builder: (_) => const PeminjamanFormScreen(),
                      ),
                    );
                  },
                  icon: const Icon(Icons.add),
                  label: const Text('Ajukan Peminjaman Baru'),
                  style: ElevatedButton.styleFrom(
                    padding: const EdgeInsets.symmetric(vertical: 16),
                  ),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}

class _UserStatCard extends StatelessWidget {
  final IconData icon;
  final String label;
  final String value;
  final Color color;

  const _UserStatCard({
    required this.icon,
    required this.label,
    required this.value,
    required this.color,
  });

  @override
  Widget build(BuildContext context) {
    return Card(
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          children: [
            Icon(icon, color: color, size: 32),
            const SizedBox(height: 8),
            Text(
              value,
              style: Theme.of(context).textTheme.headlineSmall?.copyWith(
                    fontWeight: FontWeight.bold,
                    color: color,
                  ),
            ),
            Text(
              label,
              style: Theme.of(context).textTheme.bodySmall?.copyWith(
                    color: AppColors.textSecondary,
                  ),
              textAlign: TextAlign.center,
            ),
          ],
        ),
      ),
    );
  }
}
