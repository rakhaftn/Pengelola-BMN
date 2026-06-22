import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:pull_to_refresh/pull_to_refresh.dart';
import '../../../core/constants/app_colors.dart';
import '../../../data/models/barang_model.dart';
import '../../providers/barang_provider.dart';
import '../../widgets/barang_widgets.dart';
import '../../widgets/common_widgets.dart';
import 'barang_detail_screen.dart';

/// Barang list screen
class BarangListScreen extends ConsumerStatefulWidget {
  final bool isUserView;

  const BarangListScreen({
    super.key,
    this.isUserView = false,
  });

  @override
  ConsumerState<BarangListScreen> createState() => _BarangListScreenState();
}

class _BarangListScreenState extends ConsumerState<BarangListScreen> {
  final _searchController = TextEditingController();
  final _refreshController = RefreshController();
  final _scrollController = ScrollController();

  @override
  void initState() {
    super.initState();
    _scrollController.addListener(_onScroll);
    Future.microtask(() {
      ref.read(barangListProvider.notifier).loadBarang(refresh: true);
    });
  }

  @override
  void dispose() {
    _searchController.dispose();
    _refreshController.dispose();
    _scrollController.dispose();
    super.dispose();
  }

  void _onScroll() {
    if (_scrollController.position.pixels >=
        _scrollController.position.maxScrollExtent - 200) {
      ref.read(barangListProvider.notifier).loadBarang();
    }
  }

  Future<void> _onRefresh() async {
    await ref.read(barangListProvider.notifier).refresh();
    _refreshController.refreshCompleted();
  }

  @override
  Widget build(BuildContext context) {
    final state = ref.watch(barangListProvider);

    return Scaffold(
      appBar: AppBar(
        title: Text(widget.isUserView ? 'Barang Tersedia' : 'Daftar Barang'),
        automaticallyImplyLeading: false,
        actions: [
          IconButton(
            icon: const Icon(Icons.filter_list),
            onPressed: () => _showFilterDialog(context),
          ),
        ],
      ),
      body: Column(
        children: [
          // Search bar
          Padding(
            padding: const EdgeInsets.all(16),
            child: TextField(
              controller: _searchController,
              decoration: InputDecoration(
                hintText: 'Cari barang...',
                prefixIcon: const Icon(Icons.search),
                suffixIcon: _searchController.text.isNotEmpty
                    ? IconButton(
                        icon: const Icon(Icons.clear),
                        onPressed: () {
                          _searchController.clear();
                          ref.read(barangListProvider.notifier).setSearchQuery(null);
                        },
                      )
                    : null,
                filled: true,
                fillColor: AppColors.surface,
                border: OutlineInputBorder(
                  borderRadius: BorderRadius.circular(12),
                  borderSide: BorderSide.none,
                ),
              ),
              onChanged: (value) {
                ref.read(barangListProvider.notifier).setSearchQuery(value);
              },
            ),
          ),
          // Active filters
          if (state.kategoriFilter != null || state.lokasiFilter != null)
            Padding(
              padding: const EdgeInsets.symmetric(horizontal: 16),
              child: Row(
                children: [
                  if (state.kategoriFilter != null)
                    Chip(
                      label: Text('Kategori: ${state.kategoriFilter}'),
                      onDeleted: () {
                        ref.read(barangListProvider.notifier).setKategoriFilter(null);
                      },
                    ),
                  if (state.lokasiFilter != null) ...[
                    const SizedBox(width: 8),
                    Chip(
                      label: Text('Lokasi: ${state.lokasiFilter}'),
                      onDeleted: () {
                        ref.read(barangListProvider.notifier).setLokasiFilter(null);
                      },
                    ),
                  ],
                  const Spacer(),
                  TextButton(
                    onPressed: () {
                      ref.read(barangListProvider.notifier).clearFilters();
                    },
                    child: const Text('Clear'),
                  ),
                ],
              ),
            ),
          // List
          Expanded(
            child: state.isLoading && state.items.isEmpty
                ? const LoadingIndicator(message: 'Memuat data...')
                : state.error != null && state.items.isEmpty
                    ? ErrorState(
                        message: state.error!,
                        onRetry: () => ref.read(barangListProvider.notifier).refresh(),
                      )
                    : state.items.isEmpty
                        ? const EmptyState(
                            icon: Icons.inventory_2,
                            title: 'Tidak Ada Barang',
                            message: 'Belum ada data barang',
                          )
                        : SmartRefresher(
                            controller: _refreshController,
                            onRefresh: _onRefresh,
                            child: ListView.builder(
                              controller: _scrollController,
                              padding: const EdgeInsets.only(bottom: 16),
                              itemCount: state.items.length + (state.hasMore ? 1 : 0),
                              itemBuilder: (context, index) {
                                if (index >= state.items.length) {
                                  return const Padding(
                                    padding: EdgeInsets.all(16),
                                    child: LoadingIndicator(),
                                  );
                                }
                                final barang = state.items[index];
                                return BarangCard(
                                  barang: barang,
                                  onTap: () => _navigateToDetail(context, barang),
                                );
                              },
                            ),
                          ),
          ),
        ],
      ),
    );
  }

  void _navigateToDetail(BuildContext context, Barang barang) {
    Navigator.push(
      context,
      MaterialPageRoute(
        builder: (_) => BarangDetailScreen(barang: barang),
      ),
    );
  }

  void _showFilterDialog(BuildContext context) {
    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(20)),
      ),
      builder: (context) => const _FilterBottomSheet(),
    );
  }
}

class _FilterBottomSheet extends ConsumerWidget {
  const _FilterBottomSheet();

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final kategoriAsync = ref.watch(kategoriListProvider);
    final lokasiAsync = ref.watch(lokasiListProvider);
    final state = ref.watch(barangListProvider);

    return DraggableScrollableSheet(
      initialChildSize: 0.6,
      minChildSize: 0.3,
      maxChildSize: 0.9,
      expand: false,
      builder: (context, scrollController) {
        return Padding(
          padding: const EdgeInsets.all(16),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Center(
                child: Container(
                  width: 40,
                  height: 4,
                  decoration: BoxDecoration(
                    color: AppColors.border,
                    borderRadius: BorderRadius.circular(2),
                  ),
                ),
              ),
              const SizedBox(height: 16),
              Text(
                'Filter',
                style: Theme.of(context).textTheme.titleLarge?.copyWith(
                      fontWeight: FontWeight.bold,
                    ),
              ),
              const SizedBox(height: 16),
              Expanded(
                child: ListView(
                  controller: scrollController,
                  children: [
                    // Kategori filter
                    Text(
                      'Kategori',
                      style: Theme.of(context).textTheme.titleSmall?.copyWith(
                            fontWeight: FontWeight.w600,
                          ),
                    ),
                    const SizedBox(height: 8),
                    kategoriAsync.when(
                      data: (kategori) => Wrap(
                        spacing: 8,
                        runSpacing: 8,
                        children: [
                          FilterChip(
                            label: const Text('Semua'),
                            selected: state.kategoriFilter == null,
                            onSelected: (_) {
                              ref.read(barangListProvider.notifier).setKategoriFilter(null);
                            },
                          ),
                          ...kategori.map((k) => FilterChip(
                                label: Text(k.nama),
                                selected: state.kategoriFilter == k.id.toString(),
                                onSelected: (_) {
                                  ref.read(barangListProvider.notifier).setKategoriFilter(k.id.toString());
                                },
                              )),
                        ],
                      ),
                      loading: () => const CircularProgressIndicator(),
                      error: (_, __) => const Text('Gagal memuat kategori'),
                    ),
                    const SizedBox(height: 24),
                    // Lokasi filter
                    Text(
                      'Lokasi',
                      style: Theme.of(context).textTheme.titleSmall?.copyWith(
                            fontWeight: FontWeight.w600,
                          ),
                    ),
                    const SizedBox(height: 8),
                    lokasiAsync.when(
                      data: (lokasi) => Wrap(
                        spacing: 8,
                        runSpacing: 8,
                        children: [
                          FilterChip(
                            label: const Text('Semua'),
                            selected: state.lokasiFilter == null,
                            onSelected: (_) {
                              ref.read(barangListProvider.notifier).setLokasiFilter(null);
                            },
                          ),
                          ...lokasi.map((l) => FilterChip(
                                label: Text(l.nama),
                                selected: state.lokasiFilter == l.id.toString(),
                                onSelected: (_) {
                                  ref.read(barangListProvider.notifier).setLokasiFilter(l.id.toString());
                                },
                              )),
                        ],
                      ),
                      loading: () => const CircularProgressIndicator(),
                      error: (_, __) => const Text('Gagal memuat lokasi'),
                    ),
                  ],
                ),
              ),
            ],
          ),
        );
      },
    );
  }
}
