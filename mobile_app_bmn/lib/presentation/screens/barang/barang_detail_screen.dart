import 'package:flutter/material.dart';
import 'package:cached_network_image/cached_network_image.dart';
import '../../../core/constants/app_colors.dart';
import '../../../data/models/barang_model.dart';
import '../../widgets/common_widgets.dart';

/// Barang detail screen
class BarangDetailScreen extends StatelessWidget {
  final Barang barang;

  const BarangDetailScreen({
    super.key,
    required this.barang,
  });

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Detail Barang'),
      ),
      body: SingleChildScrollView(
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // Image
            Container(
              width: double.infinity,
              height: 250,
              color: AppColors.surfaceVariant,
              child: barang.foto != null && barang.foto!.isNotEmpty
                  ? CachedNetworkImage(
                      imageUrl: barang.foto!,
                      fit: BoxFit.cover,
                      placeholder: (context, url) => const Center(
                        child: CircularProgressIndicator(),
                      ),
                      errorWidget: (context, url, error) => const Icon(
                        Icons.inventory_2,
                        size: 80,
                        color: AppColors.textHint,
                      ),
                    )
                  : const Icon(
                      Icons.inventory_2,
                      size: 80,
                      color: AppColors.textHint,
                    ),
            ),
            Padding(
              padding: const EdgeInsets.all(16),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  // Title and status
                  Row(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Expanded(
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Text(
                              barang.nama,
                              style: Theme.of(context).textTheme.headlineSmall?.copyWith(
                                    fontWeight: FontWeight.bold,
                                  ),
                            ),
                            const SizedBox(height: 4),
                            Text(
                              'Kode: ${barang.kodeBarang}',
                              style: Theme.of(context).textTheme.bodyMedium?.copyWith(
                                    color: AppColors.textSecondary,
                                  ),
                            ),
                          ],
                        ),
                      ),
                      Column(
                        crossAxisAlignment: CrossAxisAlignment.end,
                        children: [
                          StatusChip(status: barang.displayStatus),
                          const SizedBox(height: 4),
                          KondisiChip(kondisi: barang.kondisi),
                        ],
                      ),
                    ],
                  ),
                  const SizedBox(height: 24),
                  // Info section
                  _buildSectionTitle(context, 'Informasi'),
                  const SizedBox(height: 12),
                  _buildInfoCard(
                    context,
                    children: [
                      if (barang.kategori != null)
                        _buildInfoRow(
                          context,
                          icon: Icons.category,
                          label: 'Kategori',
                          value: barang.kategori!.nama,
                        ),
                      if (barang.lokasi != null || barang.ruangan != null)
                        _buildInfoRow(
                          context,
                          icon: Icons.location_on,
                          label: 'Lokasi',
                          value: barang.lokasi?.nama ?? barang.ruangan?.nama ?? '-',
                        ),
                      if (barang.gedung != null)
                        _buildInfoRow(
                          context,
                          icon: Icons.business,
                          label: 'Gedung',
                          value: barang.gedung!.nama,
                        ),
                      _buildInfoRow(
                        context,
                        icon: Icons.check_circle,
                        label: 'Kondisi',
                        value: barang.displayKondisi,
                      ),
                      _buildInfoRow(
                        context,
                        icon: Icons.info,
                        label: 'Status',
                        value: barang.displayStatus,
                      ),
                      if (barang.merek != null)
                        _buildInfoRow(
                          context,
                          icon: Icons.branding_watermark,
                          label: 'Merek/Model',
                          value: barang.merek!,
                        ),
                      if (barang.nomorSeri != null)
                        _buildInfoRow(
                          context,
                          icon: Icons.tag,
                          label: 'Nomor Seri',
                          value: barang.nomorSeri!,
                        ),
                      if (barang.qrUrl != null)
                        _buildInfoRow(
                          context,
                          icon: Icons.qr_code,
                          label: 'QR Code URL',
                          value: barang.qrUrl!,
                        ),
                    ],
                  ),
                  if (barang.keterangan != null && barang.keterangan!.isNotEmpty) ...[
                    const SizedBox(height: 24),
                    _buildSectionTitle(context, 'Keterangan'),
                    const SizedBox(height: 12),
                    Card(
                      child: Padding(
                        padding: const EdgeInsets.all(16),
                        child: Text(
                          barang.keterangan!,
                          style: Theme.of(context).textTheme.bodyMedium,
                        ),
                      ),
                    ),
                  ],
                  const SizedBox(height: 32),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildSectionTitle(BuildContext context, String title) {
    return Text(
      title,
      style: Theme.of(context).textTheme.titleMedium?.copyWith(
            fontWeight: FontWeight.bold,
          ),
    );
  }

  Widget _buildInfoCard(BuildContext context, {required List<Widget> children}) {
    return Card(
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          children: children,
        ),
      ),
    );
  }

  Widget _buildInfoRow(
    BuildContext context, {
    required IconData icon,
    required String label,
    required String value,
  }) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 8),
      child: Row(
        children: [
          Container(
            padding: const EdgeInsets.all(8),
            decoration: BoxDecoration(
              color: AppColors.primary.withValues(alpha: 0.1),
              borderRadius: BorderRadius.circular(8),
            ),
            child: Icon(icon, size: 20, color: AppColors.primary),
          ),
          const SizedBox(width: 12),
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
                      ),
                  maxLines: 2,
                  overflow: TextOverflow.ellipsis,
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }
}
