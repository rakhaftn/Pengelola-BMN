import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:pull_to_refresh/pull_to_refresh.dart';
import '../../../core/constants/app_colors.dart';
import '../../providers/auth_provider.dart';
import '../../providers/peminjaman_provider.dart';
import '../../widgets/peminjaman_widgets.dart';
import '../../widgets/common_widgets.dart';
import 'peminjaman_detail_screen.dart';

/// Peminjaman list screen (admin view)
class PeminjamanListScreen extends ConsumerStatefulWidget {
  final bool isAdminView;

  const PeminjamanListScreen({
    super.key,
    this.isAdminView = false,
  });

  @override
  ConsumerState<PeminjamanListScreen> createState() => _PeminjamanListScreenState();
}

class _PeminjamanListScreenState extends ConsumerState<PeminjamanListScreen> {
  final _refreshController = RefreshController();
  String? _selectedStatus;

  @override
  void initState() {
    super.initState();
    Future.microtask(() {
      if (widget.isAdminView) {
        ref.read(adminPeminjamanProvider.notifier).loadPeminjaman(refresh: true);
      } else {
        final userId = ref.read(authProvider).user?.id ?? 0;
        ref.read(userPeminjamanProvider(userId).notifier).loadPeminjaman(refresh: true);
      }
    });
  }

  @override
  void dispose() {
    _refreshController.dispose();
    super.dispose();
  }

  Future<void> _onRefresh() async {
    if (widget.isAdminView) {
      await ref.read(adminPeminjamanProvider.notifier).refresh();
    } else {
      final userId = ref.read(authProvider).user?.id ?? 0;
      await ref.read(userPeminjamanProvider(userId).notifier).refresh();
    }
    _refreshController.refreshCompleted();
  }

  @override
  Widget build(BuildContext context) {
    final userId = ref.watch(authProvider).user?.id ?? 0;
    final state = widget.isAdminView
        ? ref.watch(adminPeminjamanProvider)
        : ref.watch(userPeminjamanProvider(userId));

    return Scaffold(
      appBar: AppBar(
        title: Text(widget.isAdminView ? 'Verifikasi Peminjaman' : 'Riwayat Peminjaman'),
        automaticallyImplyLeading: false,
      ),
      body: Column(
        children: [
          // Status filter
          Padding(
            padding: const EdgeInsets.all(16),
            child: SingleChildScrollView(
              scrollDirection: Axis.horizontal,
              child: Row(
                children: [
                  _FilterChip(
                    label: 'Semua',
                    isSelected: _selectedStatus == null,
                    onTap: () {
                      setState(() => _selectedStatus = null);
                      if (widget.isAdminView) {
                        ref.read(adminPeminjamanProvider.notifier).setStatusFilter(null);
                      } else {
                        ref.read(userPeminjamanProvider(userId).notifier).setStatusFilter(null);
                      }
                    },
                  ),
                  const SizedBox(width: 8),
                  _FilterChip(
                    label: 'Menunggu',
                    isSelected: _selectedStatus == 'Menunggu',
                    color: AppColors.warning,
                    onTap: () {
                      setState(() => _selectedStatus = 'Menunggu');
                      if (widget.isAdminView) {
                        ref.read(adminPeminjamanProvider.notifier).setStatusFilter('Menunggu');
                      } else {
                        ref.read(userPeminjamanProvider(userId).notifier).setStatusFilter('Menunggu');
                      }
                    },
                  ),
                  const SizedBox(width: 8),
                  _FilterChip(
                    label: 'Disetujui',
                    isSelected: _selectedStatus == 'Disetujui',
                    color: AppColors.success,
                    onTap: () {
                      setState(() => _selectedStatus = 'Disetujui');
                      if (widget.isAdminView) {
                        ref.read(adminPeminjamanProvider.notifier).setStatusFilter('Disetujui');
                      } else {
                        ref.read(userPeminjamanProvider(userId).notifier).setStatusFilter('Disetujui');
                      }
                    },
                  ),
                  const SizedBox(width: 8),
                  _FilterChip(
                    label: 'Ditolak',
                    isSelected: _selectedStatus == 'Ditolak',
                    color: AppColors.error,
                    onTap: () {
                      setState(() => _selectedStatus = 'Ditolak');
                      if (widget.isAdminView) {
                        ref.read(adminPeminjamanProvider.notifier).setStatusFilter('Ditolak');
                      } else {
                        ref.read(userPeminjamanProvider(userId).notifier).setStatusFilter('Ditolak');
                      }
                    },
                  ),
                  const SizedBox(width: 8),
                  _FilterChip(
                    label: 'Dikembalikan',
                    isSelected: _selectedStatus == 'Dikembalikan',
                    color: AppColors.info,
                    onTap: () {
                      setState(() => _selectedStatus = 'Dikembalikan');
                      if (widget.isAdminView) {
                        ref.read(adminPeminjamanProvider.notifier).setStatusFilter('Dikembalikan');
                      } else {
                        ref.read(userPeminjamanProvider(userId).notifier).setStatusFilter('Dikembalikan');
                      }
                    },
                  ),
                ],
              ),
            ),
          ),
          // List
          Expanded(
            child: state.isLoading && state.items.isEmpty
                ? const LoadingIndicator(message: 'Memuat data...')
                : state.error != null && state.items.isEmpty
                    ? ErrorState(
                        message: state.error!,
                        onRetry: _onRefresh,
                      )
                    : state.items.isEmpty
                        ? EmptyState(
                            icon: Icons.assignment,
                            title: 'Tidak Ada Peminjaman',
                            message: _selectedStatus != null
                                ? 'Tidak ada peminjaman dengan status "$_selectedStatus"'
                                : 'Belum ada pengajuan peminjaman',
                          )
                        : SmartRefresher(
                            controller: _refreshController,
                            onRefresh: _onRefresh,
                            child: ListView.builder(
                              padding: const EdgeInsets.only(bottom: 16),
                              itemCount: state.items.length,
                              itemBuilder: (context, index) {
                                final peminjaman = state.items[index];
                                return PeminjamanCard(
                                  peminjaman: peminjaman,
                                  showUser: widget.isAdminView,
                                  onTap: () {
                                    Navigator.push(
                                      context,
                                      MaterialPageRoute(
                                        builder: (_) => PeminjamanDetailScreen(
                                          peminjamanId: peminjaman.id,
                                          isAdminView: widget.isAdminView,
                                        ),
                                      ),
                                    );
                                  },
                                );
                              },
                            ),
                          ),
          ),
        ],
      ),
    );
  }
}

class _FilterChip extends StatelessWidget {
  final String label;
  final bool isSelected;
  final Color? color;
  final VoidCallback onTap;

  const _FilterChip({
    required this.label,
    required this.isSelected,
    this.color,
    required this.onTap,
  });

  @override
  Widget build(BuildContext context) {
    final chipColor = color ?? AppColors.primary;
    return GestureDetector(
      onTap: onTap,
      child: Container(
        padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
        decoration: BoxDecoration(
          color: isSelected ? chipColor : Colors.transparent,
          borderRadius: BorderRadius.circular(20),
          border: Border.all(
            color: isSelected ? chipColor : AppColors.border,
          ),
        ),
        child: Text(
          label,
          style: TextStyle(
            color: isSelected ? Colors.white : AppColors.textSecondary,
            fontWeight: isSelected ? FontWeight.w600 : FontWeight.normal,
          ),
        ),
      ),
    );
  }
}

/// User specific peminjaman screen
class UserPeminjamanScreen extends ConsumerStatefulWidget {
  const UserPeminjamanScreen({super.key});

  @override
  ConsumerState<UserPeminjamanScreen> createState() => _UserPeminjamanScreenState();
}

class _UserPeminjamanScreenState extends ConsumerState<UserPeminjamanScreen> {
  @override
  Widget build(BuildContext context) {
    // Reuse PeminjamanListScreen with isAdminView: false
    return const PeminjamanListScreen(isAdminView: false);
  }
}
