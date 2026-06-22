import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:intl/intl.dart';
import '../../../core/constants/app_colors.dart';
import '../../../data/models/peminjaman_model.dart';
import '../../../data/repositories/peminjaman_repository.dart';
import '../../providers/auth_provider.dart';
import '../../providers/peminjaman_provider.dart';
import '../../widgets/common_widgets.dart';

/// Peminjaman detail screen
class PeminjamanDetailScreen extends ConsumerStatefulWidget {
  final int peminjamanId;
  final bool isAdminView;

  const PeminjamanDetailScreen({
    super.key,
    required this.peminjamanId,
    this.isAdminView = false,
  });

  @override
  ConsumerState<PeminjamanDetailScreen> createState() => _PeminjamanDetailScreenState();
}

class _PeminjamanDetailScreenState extends ConsumerState<PeminjamanDetailScreen> {
  bool _isProcessing = false;

  @override
  Widget build(BuildContext context) {
    final peminjamanAsync = ref.watch(peminjamanDetailProvider(widget.peminjamanId));
    final dateFormat = DateFormat('dd MMMM yyyy, HH:mm');

    return Scaffold(
      appBar: AppBar(
        title: Text(widget.isAdminView ? 'Detail Peminjaman' : 'Detail Pinjaman'),
      ),
      body: peminjamanAsync.when(
        data: (response) {
          if (!response.success || response.data == null) {
            return ErrorState(
              message: response.message ?? 'Gagal memuat data',
              onRetry: () => ref.refresh(peminjamanDetailProvider(widget.peminjamanId)),
            );
          }
          final peminjaman = response.data!;
          return _buildContent(context, peminjaman, dateFormat);
        },
        loading: () => const LoadingIndicator(message: 'Memuat data...'),
        error: (error, _) => ErrorState(
          message: error.toString(),
          onRetry: () => ref.refresh(peminjamanDetailProvider(widget.peminjamanId)),
        ),
      ),
    );
  }

  Widget _buildContent(BuildContext context, Peminjaman peminjaman, DateFormat dateFormat) {
    return SingleChildScrollView(
      padding: const EdgeInsets.all(16),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // Header card
          Card(
            child: Padding(
              padding: const EdgeInsets.all(16),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Row(
                    children: [
                      Container(
                        padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
                        decoration: BoxDecoration(
                          color: AppColors.primary.withValues(alpha: 0.1),
                          borderRadius: BorderRadius.circular(8),
                        ),
                        child: Text(
                          '#${peminjaman.nomorPeminjaman}',
                          style: const TextStyle(
                            color: AppColors.primary,
                            fontWeight: FontWeight.bold,
                            fontSize: 16,
                          ),
                        ),
                      ),
                      const Spacer(),
                      StatusChip(status: peminjaman.displayStatus),
                    ],
                  ),
                  const SizedBox(height: 16),
                  if (widget.isAdminView && peminjaman.peminjamName != null) ...[
                    _buildInfoRow(
                      context,
                      icon: Icons.person,
                      label: 'Peminjam',
                      value: peminjaman.peminjamName!,
                    ),
                    const SizedBox(height: 12),
                  ],
                  _buildInfoRow(
                    context,
                    icon: Icons.calendar_today,
                    label: 'Tanggal Pinjam',
                    value: dateFormat.format(peminjaman.tanggalPinjam),
                  ),
                  if (peminjaman.tanggalKembaliRencana != null) ...[
                    const SizedBox(height: 12),
                    _buildInfoRow(
                      context,
                      icon: Icons.event,
                      label: 'Rencana Kembali',
                      value: dateFormat.format(peminjaman.tanggalKembaliRencana!),
                    ),
                  ],
                  if (peminjaman.tanggalKembali != null) ...[
                    const SizedBox(height: 12),
                    _buildInfoRow(
                      context,
                      icon: Icons.check_circle,
                      label: 'Tanggal Kembali',
                      value: dateFormat.format(peminjaman.tanggalKembali!),
                      valueColor: AppColors.success,
                    ),
                  ],
                  const SizedBox(height: 12),
                  _buildInfoRow(
                    context,
                    icon: Icons.flag,
                    label: 'Tujuan',
                    value: peminjaman.tujuan,
                  ),
                  if (peminjaman.keperluan != null) ...[
                    const SizedBox(height: 12),
                    _buildInfoRow(
                      context,
                      icon: Icons.note,
                      label: 'Keperluan',
                      value: peminjaman.keperluan!,
                    ),
                  ],
                ],
              ),
            ),
          ),
          const SizedBox(height: 16),
          // Items card
          Card(
            child: Padding(
              padding: const EdgeInsets.all(16),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    'Daftar Barang (${peminjaman.jumlahBarang})',
                    style: Theme.of(context).textTheme.titleMedium?.copyWith(
                          fontWeight: FontWeight.bold,
                        ),
                  ),
                  const SizedBox(height: 12),
                  if (peminjaman.details != null && peminjaman.details!.isNotEmpty)
                    ...peminjaman.details!.map((detail) => ListTile(
                          contentPadding: EdgeInsets.zero,
                          leading: Container(
                            width: 48,
                            height: 48,
                            decoration: BoxDecoration(
                              color: AppColors.surfaceVariant,
                              borderRadius: BorderRadius.circular(8),
                            ),
                            child: const Icon(Icons.inventory_2, color: AppColors.textHint),
                          ),
                          title: Text(detail.barang?.nama ?? 'Barang #${detail.barangId}'),
                          subtitle: Text(detail.barang?.kodeBarang ?? '-'),
                          trailing: KondisiChip(kondisi: detail.barang?.kondisi ?? 'baik'),
                        ))
                  else
                    const Text('Tidak ada data barang'),
                ],
              ),
            ),
          ),
          // Notes
          if (peminjaman.alasanPenolakan != null && peminjaman.alasanPenolakan!.isNotEmpty) ...[
            const SizedBox(height: 16),
            Card(
              color: AppColors.warningLight,
              child: Padding(
                padding: const EdgeInsets.all(16),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Row(
                      children: [
                        const Icon(Icons.info, size: 20, color: AppColors.warning),
                        const SizedBox(width: 8),
                        Text(
                          'Alasan Penolakan',
                          style: Theme.of(context).textTheme.titleMedium?.copyWith(
                                fontWeight: FontWeight.bold,
                                color: AppColors.warning,
                              ),
                        ),
                      ],
                    ),
                    const SizedBox(height: 8),
                    Text(peminjaman.alasanPenolakan!),
                  ],
                ),
              ),
            ),
          ],
          const SizedBox(height: 24),
          // Action buttons
          _buildActionButtons(context, peminjaman),
        ],
      ),
    );
  }

  Widget _buildInfoRow(
    BuildContext context, {
    required IconData icon,
    required String label,
    required String value,
    Color? valueColor,
  }) {
    return Row(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Icon(icon, size: 20, color: AppColors.textSecondary),
        const SizedBox(width: 8),
        Expanded(
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text(
                label,
                style: Theme.of(context).textTheme.bodySmall?.copyWith(
                      color: AppColors.textSecondary,
                    ),
              ),
              Text(
                value,
                style: Theme.of(context).textTheme.bodyMedium?.copyWith(
                      fontWeight: FontWeight.w500,
                      color: valueColor,
                    ),
              ),
            ],
          ),
        ),
      ],
    );
  }

  Widget _buildActionButtons(BuildContext context, Peminjaman peminjaman) {
    if (widget.isAdminView) {
      // Admin actions
      if (peminjaman.canApprove) {
        return Column(
          crossAxisAlignment: CrossAxisAlignment.stretch,
          children: [
            ElevatedButton.icon(
              onPressed: _isProcessing ? null : () => _approvePeminjaman(peminjaman.id),
              icon: _isProcessing
                  ? const SizedBox(
                      width: 20,
                      height: 20,
                      child: CircularProgressIndicator(strokeWidth: 2, color: Colors.white),
                    )
                  : const Icon(Icons.check),
              label: const Text('Setujui Peminjaman'),
              style: ElevatedButton.styleFrom(
                backgroundColor: AppColors.success,
                padding: const EdgeInsets.symmetric(vertical: 16),
              ),
            ),
            const SizedBox(height: 12),
            OutlinedButton.icon(
              onPressed: _isProcessing ? null : () => _showRejectDialog(peminjaman.id),
              icon: const Icon(Icons.close),
              label: const Text('Tolak Peminjaman'),
              style: OutlinedButton.styleFrom(
                foregroundColor: AppColors.error,
                side: const BorderSide(color: AppColors.error),
                padding: const EdgeInsets.symmetric(vertical: 16),
              ),
            ),
          ],
        );
      }
    } else {
      // User actions
      if (peminjaman.canSubmit) {
        return SizedBox(
          width: double.infinity,
          child: ElevatedButton.icon(
            onPressed: _isProcessing ? null : () => _submitPeminjaman(peminjaman.id),
            icon: _isProcessing
                ? const SizedBox(
                    width: 20,
                    height: 20,
                    child: CircularProgressIndicator(strokeWidth: 2, color: Colors.white),
                  )
                : const Icon(Icons.send),
            label: const Text('Ajukan Peminjaman'),
            style: ElevatedButton.styleFrom(
              backgroundColor: AppColors.primary,
              padding: const EdgeInsets.symmetric(vertical: 16),
            ),
          ),
        );
      }
      if (peminjaman.canSerahTerima) {
        return SizedBox(
          width: double.infinity,
          child: ElevatedButton.icon(
            onPressed: _isProcessing ? null : () => _serahTerima(peminjaman.id),
            icon: _isProcessing
                ? const SizedBox(
                    width: 20,
                    height: 20,
                    child: CircularProgressIndicator(strokeWidth: 2, color: Colors.white),
                  )
                : const Icon(Icons.handshake),
            label: const Text('Serah Terima Barang'),
            style: ElevatedButton.styleFrom(
              backgroundColor: AppColors.secondary,
              padding: const EdgeInsets.symmetric(vertical: 16),
            ),
          ),
        );
      }
    }
    return const SizedBox.shrink();
  }

  Future<void> _submitPeminjaman(int id) async {
    setState(() => _isProcessing = true);

    final repository = ref.read(peminjamanRepositoryProvider);
    final response = await repository.submitPeminjaman(id);

    setState(() => _isProcessing = false);

    if (mounted) {
      if (response.success) {
        showSuccessSnackBar(context, 'Peminjaman berhasil diajukan');
        ref.refresh(peminjamanDetailProvider(id));
        final userId = ref.read(authProvider).user?.id ?? 0;
        ref.read(userPeminjamanProvider(userId).notifier).refresh();
      } else {
        showErrorSnackBar(context, response.message ?? 'Gagal mengajukan');
      }
    }
  }

  Future<void> _approvePeminjaman(int id) async {
    setState(() => _isProcessing = true);

    final repository = ref.read(peminjamanRepositoryProvider);
    final response = await repository.approvePeminjaman(id: id);

    setState(() => _isProcessing = false);

    if (mounted) {
      if (response.success) {
        showSuccessSnackBar(context, 'Peminjaman berhasil disetujui');
        ref.refresh(peminjamanDetailProvider(id));
        ref.read(adminPeminjamanProvider.notifier).refresh();
      } else {
        showErrorSnackBar(context, response.message ?? 'Gagal menyetujui');
      }
    }
  }

  Future<void> _serahTerima(int id) async {
    setState(() => _isProcessing = true);

    final repository = ref.read(peminjamanRepositoryProvider);
    final response = await repository.serahTerima(id);

    setState(() => _isProcessing = false);

    if (mounted) {
      if (response.success) {
        showSuccessSnackBar(context, 'Barang berhasil diserahterimakan');
        ref.refresh(peminjamanDetailProvider(id));
        ref.read(adminPeminjamanProvider.notifier).refresh();
      } else {
        showErrorSnackBar(context, response.message ?? 'Gagal serah terima');
      }
    }
  }

  void _showRejectDialog(int id) {
    final controller = TextEditingController();
    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text('Tolak Peminjaman'),
        content: TextField(
          controller: controller,
          maxLines: 3,
          decoration: const InputDecoration(
            labelText: 'Alasan penolakan',
            hintText: 'Masukkan alasan penolakan...',
          ),
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context),
            child: const Text('Batal'),
          ),
          ElevatedButton(
            onPressed: () async {
              if (controller.text.isEmpty) {
                showErrorSnackBar(context, 'Alasan penolakan wajib diisi');
                return;
              }
              Navigator.pop(context);
              await _rejectPeminjaman(id, controller.text);
            },
            style: ElevatedButton.styleFrom(backgroundColor: AppColors.error),
            child: const Text('Tolak'),
          ),
        ],
      ),
    );
  }

  Future<void> _rejectPeminjaman(int id, String alasan) async {
    setState(() => _isProcessing = true);

    final repository = ref.read(peminjamanRepositoryProvider);
    final response = await repository.rejectPeminjaman(id: id, alasanPenolakan: alasan);

    setState(() => _isProcessing = false);

    if (mounted) {
      if (response.success) {
        showSuccessSnackBar(context, 'Peminjaman ditolak');
        ref.refresh(peminjamanDetailProvider(id));
        ref.read(adminPeminjamanProvider.notifier).refresh();
      } else {
        showErrorSnackBar(context, response.message ?? 'Gagal menolak');
      }
    }
  }
}
