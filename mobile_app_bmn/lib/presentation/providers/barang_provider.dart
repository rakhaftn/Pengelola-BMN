import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../data/models/barang_model.dart';
import '../../data/repositories/barang_repository.dart';
import '../../data/repositories/api_client.dart';

/// Barang list state
class BarangListState {
  final List<Barang> items;
  final bool isLoading;
  final bool hasMore;
  final int currentPage;
  final String? error;
  final String? searchQuery;
  final String? kategoriFilter;
  final String? lokasiFilter;

  const BarangListState({
    this.items = const [],
    this.isLoading = false,
    this.hasMore = true,
    this.currentPage = 1,
    this.error,
    this.searchQuery,
    this.kategoriFilter,
    this.lokasiFilter,
  });

  BarangListState copyWith({
    List<Barang>? items,
    bool? isLoading,
    bool? hasMore,
    int? currentPage,
    String? error,
    String? searchQuery,
    String? kategoriFilter,
    String? lokasiFilter,
  }) {
    return BarangListState(
      items: items ?? this.items,
      isLoading: isLoading ?? this.isLoading,
      hasMore: hasMore ?? this.hasMore,
      currentPage: currentPage ?? this.currentPage,
      error: error,
      searchQuery: searchQuery ?? this.searchQuery,
      kategoriFilter: kategoriFilter ?? this.kategoriFilter,
      lokasiFilter: lokasiFilter ?? this.lokasiFilter,
    );
  }
}

/// Barang list notifier
class BarangListNotifier extends StateNotifier<BarangListState> {
  final BarangRepository _repository;

  BarangListNotifier(this._repository) : super(const BarangListState());

  Future<void> loadBarang({bool refresh = false}) async {
    if (state.isLoading) return;
    if (!refresh && !state.hasMore && !refresh) return;

    final page = refresh ? 1 : state.currentPage;

    state = state.copyWith(
      isLoading: true,
      error: null,
      items: refresh ? [] : state.items,
      currentPage: page,
    );

    final response = await _repository.getBarang(
      page: page,
      search: state.searchQuery,
      kategoriId: state.kategoriFilter,
      lokasiId: state.lokasiFilter,
    );

    if (response.success && response.data != null) {
      final newItems = refresh
          ? response.data!.data
          : [...state.items, ...response.data!.data];

      state = state.copyWith(
        items: newItems,
        isLoading: false,
        hasMore: response.data!.hasMore,
        currentPage: response.data!.currentPage + 1,
      );
    } else {
      state = state.copyWith(
        isLoading: false,
        error: response.message,
      );
    }
  }

  Future<void> refresh() => loadBarang(refresh: true);

  void setSearchQuery(String? query) {
    state = state.copyWith(searchQuery: query);
    loadBarang(refresh: true);
  }

  void setKategoriFilter(String? kategoriId) {
    state = state.copyWith(kategoriFilter: kategoriId);
    loadBarang(refresh: true);
  }

  void setLokasiFilter(String? lokasiId) {
    state = state.copyWith(lokasiFilter: lokasiId);
    loadBarang(refresh: true);
  }

  void clearFilters() {
    state = const BarangListState();
    loadBarang(refresh: true);
  }
}

/// Barang list provider
final barangListProvider = StateNotifierProvider<BarangListNotifier, BarangListState>((ref) {
  return BarangListNotifier(ref.read(barangRepositoryProvider));
});

/// Single barang provider (by ID)
final barangDetailProvider = FutureProvider.family<ApiResponse<Barang>, int>((ref, id) async {
  final repository = ref.read(barangRepositoryProvider);
  return repository.getBarangById(id);
});

/// Scan barang provider
final scanBarangProvider = FutureProvider.family<ApiResponse<Barang>, String>((ref, kode) async {
  final repository = ref.read(barangRepositoryProvider);
  return repository.scanBarang(kode);
});

/// Kategori list provider
final kategoriListProvider = FutureProvider<List<Kategori>>((ref) async {
  final repository = ref.read(barangRepositoryProvider);
  final response = await repository.getKategori();
  return response.success ? (response.data ?? []) : [];
});

/// Lokasi list provider
final lokasiListProvider = FutureProvider<List<Lokasi>>((ref) async {
  final repository = ref.read(barangRepositoryProvider);
  final response = await repository.getLokasi();
  return response.success ? (response.data ?? []) : [];
});

/// Selected barang for detail view
final selectedBarangProvider = StateProvider<Barang?>((ref) => null);
