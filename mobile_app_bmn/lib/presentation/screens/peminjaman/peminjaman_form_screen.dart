import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:intl/intl.dart';
import '../../../core/constants/app_colors.dart';
import '../../../data/models/barang_model.dart';
import '../../../data/repositories/peminjaman_repository.dart';
import '../../providers/auth_provider.dart';
import '../../providers/peminjaman_provider.dart';
import '../../widgets/barang_widgets.dart';
import '../../widgets/common_widgets.dart';

/// Form screen for creating new loan request
class PeminjamanFormScreen extends ConsumerStatefulWidget {
  const PeminjamanFormScreen({super.key});

  @override
  ConsumerState<PeminjamanFormScreen> createState() => _PeminjamanFormScreenState();
}

class _PeminjamanFormScreenState extends ConsumerState<PeminjamanFormScreen> {
  final _formKey = GlobalKey<FormState>();
  final _tujuanController = TextEditingController();
  final _keperluanController = TextEditingController();
  DateTime _tanggalPinjam = DateTime.now();
  DateTime _tanggalKembali = DateTime.now().add(const Duration(days: 7));
  bool _isSubmitting = false;
  final _selectedBarang = <Barang>[];

  @override
  void dispose() {
    _tujuanController.dispose();
    _keperluanController.dispose();
    super.dispose();
  }

  Future<void> _selectTanggalPinjam() async {
    final picked = await showDatePicker(
      context: context,
      initialDate: _tanggalPinjam,
      firstDate: DateTime.now(),
      lastDate: DateTime.now().add(const Duration(days: 90)),
    );
    if (picked != null) {
      setState(() {
        _tanggalPinjam = picked;
        if (_tanggalKembali.isBefore(_tanggalPinjam)) {
          _tanggalKembali = _tanggalPinjam.add(const Duration(days: 1));
        }
      });
    }
  }

  Future<void> _selectTanggalKembali() async {
    final picked = await showDatePicker(
      context: context,
      initialDate: _tanggalKembali,
      firstDate: _tanggalPinjam.add(const Duration(days: 1)),
      lastDate: DateTime.now().add(const Duration(days: 90)),
    );
    if (picked != null) {
      setState(() => _tanggalKembali = picked);
    }
  }

  Future<void> _submitPeminjaman() async {
    if (!_formKey.currentState!.validate()) return;

    if (_selectedBarang.isEmpty) {
      showErrorSnackBar(context, 'Pilih minimal 1 barang');
      return;
    }

    setState(() => _isSubmitting = true);

    final repository = ref.read(peminjamanRepositoryProvider);
    final userId = ref.read(authProvider).user?.id ?? 0;

    final response = await repository.createPeminjaman(
      peminjamId: userId,
      tanggalPinjam: _tanggalPinjam,
      tanggalKembaliRencana: _tanggalKembali,
      tujuan: _tujuanController.text.trim(),
      keperluan: _keperluanController.text.isNotEmpty
          ? _keperluanController.text.trim()
          : null,
      barangIds: _selectedBarang.map((b) => b.id).toList(),
    );

    setState(() => _isSubmitting = false);

    if (response.success) {
      if (mounted) {
        showSuccessSnackBar(context, 'Peminjaman berhasil dibuat');
        Navigator.pop(context);
      }
    } else {
      if (mounted) {
        showErrorSnackBar(context, response.message ?? 'Gagal membuat peminjaman');
      }
    }
  }

  void _showBarangSelection() {
    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(20)),
      ),
      builder: (context) => _BarangSelectionSheet(
        selectedBarang: _selectedBarang,
        onSelectionChanged: (barang) {
          setState(() => _selectedBarang.clear());
          _selectedBarang.addAll(barang);
        },
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    final dateFormat = DateFormat('dd MMM yyyy');

    return Scaffold(
      appBar: AppBar(
        title: const Text('Ajukan Peminjaman'),
      ),
      body: Form(
        key: _formKey,
        child: Column(
          children: [
            Expanded(
              child: SingleChildScrollView(
                padding: const EdgeInsets.all(16),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    // Date selection
                    Card(
                      child: Padding(
                        padding: const EdgeInsets.all(16),
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Text(
                              'Tanggal Peminjaman',
                              style: Theme.of(context).textTheme.titleSmall?.copyWith(
                                    fontWeight: FontWeight.bold,
                                  ),
                            ),
                            const SizedBox(height: 8),
                            InkWell(
                              onTap: _selectTanggalPinjam,
                              child: Container(
                                padding: const EdgeInsets.all(12),
                                decoration: BoxDecoration(
                                  border: Border.all(color: AppColors.border),
                                  borderRadius: BorderRadius.circular(8),
                                ),
                                child: Row(
                                  children: [
                                    const Icon(Icons.calendar_today, color: AppColors.primary, size: 20),
                                    const SizedBox(width: 8),
                                    Text(dateFormat.format(_tanggalPinjam)),
                                  ],
                                ),
                              ),
                            ),
                            const SizedBox(height: 16),
                            Text(
                              'Rencana Tanggal Kembali',
                              style: Theme.of(context).textTheme.titleSmall?.copyWith(
                                    fontWeight: FontWeight.bold,
                                  ),
                            ),
                            const SizedBox(height: 8),
                            InkWell(
                              onTap: _selectTanggalKembali,
                              child: Container(
                                padding: const EdgeInsets.all(12),
                                decoration: BoxDecoration(
                                  border: Border.all(color: AppColors.border),
                                  borderRadius: BorderRadius.circular(8),
                                ),
                                child: Row(
                                  children: [
                                    const Icon(Icons.event, color: AppColors.primary, size: 20),
                                    const SizedBox(width: 8),
                                    Text(dateFormat.format(_tanggalKembali)),
                                  ],
                                ),
                              ),
                            ),
                          ],
                        ),
                      ),
                    ),
                    const SizedBox(height: 16),
                    // Purpose
                    Card(
                      child: Padding(
                        padding: const EdgeInsets.all(16),
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Text(
                              'Tujuan Peminjaman *',
                              style: Theme.of(context).textTheme.titleSmall?.copyWith(
                                    fontWeight: FontWeight.bold,
                                  ),
                            ),
                            const SizedBox(height: 8),
                            TextFormField(
                              controller: _tujuanController,
                              maxLines: 2,
                              decoration: const InputDecoration(
                                hintText: 'Contoh: Meeting dengan tamu kantor',
                              ),
                              validator: (value) {
                                if (value == null || value.trim().isEmpty) {
                                  return 'Tujuan wajib diisi';
                                }
                                return null;
                              },
                            ),
                            const SizedBox(height: 16),
                            Text(
                              'Keperluan (Opsional)',
                              style: Theme.of(context).textTheme.titleSmall?.copyWith(
                                    fontWeight: FontWeight.bold,
                                  ),
                            ),
                            const SizedBox(height: 8),
                            TextFormField(
                              controller: _keperluanController,
                              maxLines: 3,
                              decoration: const InputDecoration(
                                hintText: 'Tambahkan keterangan tambahan...',
                              ),
                            ),
                          ],
                        ),
                      ),
                    ),
                    const SizedBox(height: 16),
                    // Selected items
                    Card(
                      child: Padding(
                        padding: const EdgeInsets.all(16),
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Row(
                              children: [
                                Text(
                                  'Barang yang Dipinjam',
                                  style: Theme.of(context).textTheme.titleSmall?.copyWith(
                                        fontWeight: FontWeight.bold,
                                      ),
                                ),
                                const Spacer(),
                                TextButton.icon(
                                  onPressed: _showBarangSelection,
                                  icon: const Icon(Icons.add, size: 18),
                                  label: const Text('Pilih'),
                                ),
                              ],
                            ),
                            const SizedBox(height: 8),
                            if (_selectedBarang.isEmpty)
                              Container(
                                padding: const EdgeInsets.all(24),
                                decoration: BoxDecoration(
                                  color: AppColors.surfaceVariant,
                                  borderRadius: BorderRadius.circular(8),
                                ),
                                child: Center(
                                  child: Column(
                                    children: [
                                      const Icon(Icons.inventory_2_outlined,
                                          size: 48, color: AppColors.textHint),
                                      const SizedBox(height: 8),
                                      Text(
                                        'Belum ada barang dipilih',
                                        style: Theme.of(context).textTheme.bodyMedium?.copyWith(
                                              color: AppColors.textHint,
                                            ),
                                      ),
                                      const SizedBox(height: 8),
                                      TextButton(
                                        onPressed: _showBarangSelection,
                                        child: const Text('Pilih Barang'),
                                      ),
                                    ],
                                  ),
                                ),
                              )
                            else
                              ...List.generate(_selectedBarang.length, (index) {
                                final barang = _selectedBarang[index];
                                return ListTile(
                                  contentPadding: EdgeInsets.zero,
                                  leading: Container(
                                    width: 40,
                                    height: 40,
                                    decoration: BoxDecoration(
                                      color: AppColors.surfaceVariant,
                                      borderRadius: BorderRadius.circular(8),
                                    ),
                                    child: const Icon(Icons.inventory_2, color: AppColors.textHint, size: 20),
                                  ),
                                  title: Text(barang.nama, maxLines: 1, overflow: TextOverflow.ellipsis),
                                  subtitle: Text(barang.kodeBarang),
                                  trailing: IconButton(
                                    icon: const Icon(Icons.remove_circle, color: AppColors.error, size: 20),
                                    onPressed: () {
                                      setState(() => _selectedBarang.removeAt(index));
                                    },
                                  ),
                                );
                              }),
                          ],
                        ),
                      ),
                    ),
                    const SizedBox(height: 80),
                  ],
                ),
              ),
            ),
            // Submit button
            Container(
              padding: const EdgeInsets.all(16),
              decoration: BoxDecoration(
                color: AppColors.surface,
                boxShadow: [
                  BoxShadow(
                    color: Colors.black.withValues(alpha: 0.1),
                    blurRadius: 8,
                    offset: const Offset(0, -2),
                  ),
                ],
              ),
              child: SafeArea(
                child: SizedBox(
                  width: double.infinity,
                  child: ElevatedButton(
                    onPressed: _isSubmitting || _selectedBarang.isEmpty ? null : _submitPeminjaman,
                    style: ElevatedButton.styleFrom(
                      padding: const EdgeInsets.symmetric(vertical: 16),
                    ),
                    child: _isSubmitting
                        ? const SizedBox(
                            height: 24,
                            width: 24,
                            child: CircularProgressIndicator(strokeWidth: 2, color: Colors.white),
                          )
                        : Text(
                            'Ajukan Peminjaman (${_selectedBarang.length})',
                            style: const TextStyle(fontSize: 16),
                          ),
                  ),
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }
}

/// Bottom sheet for selecting barang
class _BarangSelectionSheet extends ConsumerStatefulWidget {
  final List<Barang> selectedBarang;
  final ValueChanged<List<Barang>> onSelectionChanged;

  const _BarangSelectionSheet({
    required this.selectedBarang,
    required this.onSelectionChanged,
  });

  @override
  ConsumerState<_BarangSelectionSheet> createState() => _BarangSelectionSheetState();
}

class _BarangSelectionSheetState extends ConsumerState<_BarangSelectionSheet> {
  final _searchController = TextEditingController();
  String? _searchQuery;

  @override
  void dispose() {
    _searchController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final availableBarang = ref.watch(availableBarangProvider);
    final selected = [...widget.selectedBarang];

    return DraggableScrollableSheet(
      initialChildSize: 0.7,
      minChildSize: 0.5,
      maxChildSize: 0.95,
      expand: false,
      builder: (context, scrollController) {
        return Column(
          children: [
            Padding(
              padding: const EdgeInsets.all(16),
              child: Column(
                children: [
                  Container(
                    width: 40,
                    height: 4,
                    decoration: BoxDecoration(
                      color: AppColors.border,
                      borderRadius: BorderRadius.circular(2),
                    ),
                  ),
                  const SizedBox(height: 16),
                  Row(
                    children: [
                      Text(
                        'Pilih Barang',
                        style: Theme.of(context).textTheme.titleLarge?.copyWith(
                              fontWeight: FontWeight.bold,
                            ),
                      ),
                      const Spacer(),
                      ElevatedButton(
                        onPressed: () {
                          widget.onSelectionChanged(selected);
                          Navigator.pop(context);
                        },
                        child: Text('Pilih (${selected.length})'),
                      ),
                    ],
                  ),
                  const SizedBox(height: 12),
                  TextField(
                    controller: _searchController,
                    decoration: InputDecoration(
                      hintText: 'Cari barang...',
                      prefixIcon: const Icon(Icons.search),
                      suffixIcon: _searchQuery?.isNotEmpty == true
                          ? IconButton(
                              icon: const Icon(Icons.clear),
                              onPressed: () {
                                _searchController.clear();
                                setState(() => _searchQuery = null);
                              },
                            )
                          : null,
                    ),
                    onChanged: (value) => setState(() => _searchQuery = value),
                  ),
                ],
              ),
            ),
            Expanded(
              child: availableBarang.when(
                data: (barangList) {
                  final filtered = _searchQuery?.isNotEmpty == true
                      ? barangList
                          .where((b) =>
                              b.nama.toLowerCase().contains(_searchQuery!.toLowerCase()) ||
                              b.kodeBarang.toLowerCase().contains(_searchQuery!.toLowerCase()))
                          .toList()
                      : barangList;

                  if (filtered.isEmpty) {
                    return const Center(
                      child: Text('Tidak ada barang tersedia'),
                    );
                  }

                  return ListView.builder(
                    controller: scrollController,
                    itemCount: filtered.length,
                    itemBuilder: (context, index) {
                      final barang = filtered[index];
                      final isSelected = selected.any((b) => b.id == barang.id);

                      return CheckboxListTile(
                        value: isSelected,
                        onChanged: (checked) {
                          setState(() {
                            if (checked == true) {
                              if (!isSelected) selected.add(barang);
                            } else {
                              selected.removeWhere((b) => b.id == barang.id);
                            }
                          });
                        },
                        title: Text(barang.nama, maxLines: 1, overflow: TextOverflow.ellipsis),
                        subtitle: Text(
                          '${barang.kodeBarang} • ${barang.kategoriNama}',
                          maxLines: 1,
                          overflow: TextOverflow.ellipsis,
                        ),
                        secondary: Container(
                          width: 40,
                          height: 40,
                          decoration: BoxDecoration(
                            color: AppColors.surfaceVariant,
                            borderRadius: BorderRadius.circular(8),
                          ),
                          child: const Icon(Icons.inventory_2, color: AppColors.textHint, size: 20),
                        ),
                      );
                    },
                  );
                },
                loading: () => const Center(child: CircularProgressIndicator()),
                error: (_, __) => const Center(child: Text('Gagal memuat barang')),
              ),
            ),
          ],
        );
      },
    );
  }
}
