import 'package:flutter/material.dart';
import 'package:cached_network_image/cached_network_image.dart';
import '../../core/constants/app_colors.dart';
import '../../data/models/barang_model.dart';
import 'common_widgets.dart';

/// Barang card widget for list display
class BarangCard extends StatelessWidget {
  final Barang barang;
  final VoidCallback? onTap;
  final bool showCheckbox;
  final bool isSelected;
  final ValueChanged<bool?>? onCheckboxChanged;

  const BarangCard({
    super.key,
    required this.barang,
    this.onTap,
    this.showCheckbox = false,
    this.isSelected = false,
    this.onCheckboxChanged,
  });

  @override
  Widget build(BuildContext context) {
    return Card(
      margin: const EdgeInsets.symmetric(horizontal: 16, vertical: 6),
      child: InkWell(
        onTap: onTap,
        borderRadius: BorderRadius.circular(12),
        child: Padding(
          padding: const EdgeInsets.all(12),
          child: Row(
            children: [
              if (showCheckbox) ...[
                Checkbox(
                  value: isSelected,
                  onChanged: onCheckboxChanged,
                  activeColor: AppColors.primary,
                ),
                const SizedBox(width: 8),
              ],
              // Image
              Container(
                width: 60,
                height: 60,
                decoration: BoxDecoration(
                  color: AppColors.surfaceVariant,
                  borderRadius: BorderRadius.circular(8),
                ),
                child: barang.fotoUrl != null
                    ? ClipRRect(
                        borderRadius: BorderRadius.circular(8),
                        child: CachedNetworkImage(
                          imageUrl: barang.fotoUrl!,
                          fit: BoxFit.cover,
                          placeholder: (context, url) => const Icon(
                            Icons.inventory_2,
                            color: AppColors.textHint,
                          ),
                          errorWidget: (context, url, error) => const Icon(
                            Icons.inventory_2,
                            color: AppColors.textHint,
                          ),
                        ),
                      )
                    : const Icon(
                        Icons.inventory_2,
                        color: AppColors.textHint,
                      ),
              ),
              const SizedBox(width: 12),
              // Info
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      barang.namaBarang,
                      style: Theme.of(context).textTheme.titleSmall?.copyWith(
                            fontWeight: FontWeight.w600,
                          ),
                      maxLines: 1,
                      overflow: TextOverflow.ellipsis,
                    ),
                    const SizedBox(height: 4),
                    Text(
                      'Kode: ${barang.kodeBarang}',
                      style: Theme.of(context).textTheme.bodySmall?.copyWith(
                            color: AppColors.textSecondary,
                          ),
                    ),
                    if (barang.lokasiNama != null) ...[
                      const SizedBox(height: 2),
                      Row(
                        children: [
                          const Icon(
                            Icons.location_on,
                            size: 12,
                            color: AppColors.textHint,
                          ),
                          const SizedBox(width: 2),
                          Expanded(
                            child: Text(
                              barang.lokasiNama!,
                              style: Theme.of(context).textTheme.bodySmall?.copyWith(
                                    color: AppColors.textHint,
                                  ),
                              maxLines: 1,
                              overflow: TextOverflow.ellipsis,
                            ),
                          ),
                        ],
                      ),
                    ],
                    const SizedBox(height: 6),
                    Row(
                      children: [
                        KondisiChip(kondisi: barang.kondisi),
                        const SizedBox(width: 8),
                        StatusChip(status: barang.status),
                      ],
                    ),
                  ],
                ),
              ),
              if (!showCheckbox)
                const Icon(
                  Icons.chevron_right,
                  color: AppColors.textHint,
                ),
            ],
          ),
        ),
      ),
    );
  }
}

/// Barang list tile for selection
class BarangListTile extends StatelessWidget {
  final Barang barang;
  final bool isSelected;
  final VoidCallback? onTap;

  const BarangListTile({
    super.key,
    required this.barang,
    this.isSelected = false,
    this.onTap,
  });

  @override
  Widget build(BuildContext context) {
    return ListTile(
      leading: Container(
        width: 48,
        height: 48,
        decoration: BoxDecoration(
          color: AppColors.surfaceVariant,
          borderRadius: BorderRadius.circular(8),
        ),
        child: barang.fotoUrl != null
            ? ClipRRect(
                borderRadius: BorderRadius.circular(8),
                child: CachedNetworkImage(
                  imageUrl: barang.fotoUrl!,
                  fit: BoxFit.cover,
                  placeholder: (context, url) => const Icon(
                    Icons.inventory_2,
                    color: AppColors.textHint,
                  ),
                  errorWidget: (context, url, error) => const Icon(
                    Icons.inventory_2,
                    color: AppColors.textHint,
                  ),
                ),
              )
            : const Icon(
                Icons.inventory_2,
                color: AppColors.textHint,
              ),
      ),
      title: Text(
        barang.namaBarang,
        maxLines: 1,
        overflow: TextOverflow.ellipsis,
      ),
      subtitle: Text(
        '${barang.kodeBarang} • ${barang.lokasiNama ?? "-"}',
        maxLines: 1,
        overflow: TextOverflow.ellipsis,
      ),
      trailing: isSelected
          ? const Icon(Icons.check_circle, color: AppColors.primary)
          : const Icon(Icons.circle_outlined, color: AppColors.border),
      onTap: onTap,
    );
  }
}

/// Search bar widget
class BarangSearchBar extends StatelessWidget {
  final TextEditingController? controller;
  final ValueChanged<String>? onChanged;
  final VoidCallback? onClear;
  final String hintText;

  const BarangSearchBar({
    super.key,
    this.controller,
    this.onChanged,
    this.onClear,
    this.hintText = 'Cari barang...',
  });

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.all(16),
      child: TextField(
        controller: controller,
        onChanged: onChanged,
        decoration: InputDecoration(
          hintText: hintText,
          prefixIcon: const Icon(Icons.search),
          suffixIcon: controller?.text.isNotEmpty == true
              ? IconButton(
                  icon: const Icon(Icons.clear),
                  onPressed: () {
                    controller?.clear();
                    onClear?.call();
                  },
                )
              : null,
          filled: true,
          fillColor: AppColors.surface,
          border: OutlineInputBorder(
            borderRadius: BorderRadius.circular(12),
            borderSide: BorderSide.none,
          ),
          contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
        ),
      ),
    );
  }
}
