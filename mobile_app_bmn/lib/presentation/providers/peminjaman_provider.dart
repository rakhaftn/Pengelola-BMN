import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../data/models/peminjaman_model.dart';
import '../../data/models/barang_model.dart';
import '../../data/repositories/peminjaman_repository.dart';
import '../../data/repositories/api_client.dart';

/// Peminjaman list state
class PeminjamanListState {
  final List<Peminjaman> items;
  final bool isLoading;
  final bool hasMore;
  final int currentPage;
  final String? error;
  final String? statusFilter;

  const PeminjamanListState({
    this.items = const [],
    this.isLoading = false,
    this.hasMore = true,
    this.currentPage = 1,
    this.error,
    this.statusFilter,
  });

  PeminjamanListState copyWith({
    List<Peminjaman>? items,
    bool? isLoading,
    bool? hasMore,
    int? currentPage,
    String? error,
    String? statusFilter,
  }) {
    return PeminjamanListState(
      items: items ?? this.items,
      isLoading: isLoading ?? this.isLoading,
      hasMore: hasMore ?? this.hasMore,
      currentPage: currentPage ?? this.currentPage,
      error: error,
      statusFilter: statusFilter ?? this.statusFilter,
    );
  }
}

/// Peminjaman list notifier
class PeminjamanListNotifier extends StateNotifier<PeminjamanListState> {
  final PeminjamanRepository _repository;
  final int? userId;
  final bool isAdminView;

  PeminjamanListNotifier(
    this._repository, {
    this.userId,
    this.isAdminView = false,
  }) : super(const PeminjamanListState());

  Future<void> loadPeminjaman({bool refresh = false}) async {
    if (state.isLoading) return;
    if (!refresh && !state.hasMore) return;

    final page = refresh ? 1 : state.currentPage;

    state = state.copyWith(
      isLoading: true,
      error: null,
      items: refresh ? [] : state.items,
      currentPage: page,
    );

    final response = await _repository.getPeminjaman(
      page: page,
      status: state.statusFilter,
      peminjamId: isAdminView ? null : userId,
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

  Future<void> refresh() => loadPeminjaman(refresh: true);

  void setStatusFilter(String? status) {
    state = state.copyWith(statusFilter: status);
    loadPeminjaman(refresh: true);
  }

  void updateItem(Peminjaman updated) {
    final index = state.items.indexWhere((item) => item.id == updated.id);
    if (index != -1) {
      final newItems = [...state.items];
      newItems[index] = updated;
      state = state.copyWith(items: newItems);
    }
  }

  void removeItem(int id) {
    state = state.copyWith(
      items: state.items.where((item) => item.id != id).toList(),
    );
  }
}

/// User peminjaman list provider
final userPeminjamanProvider = StateNotifierProvider.family<PeminjamanListNotifier, PeminjamanListState, int>(
  (ref, userId) => PeminjamanListNotifier(
    ref.read(peminjamanRepositoryProvider),
    userId: userId,
    isAdminView: false,
  ),
);

/// Admin peminjaman list provider
final adminPeminjamanProvider = StateNotifierProvider<PeminjamanListNotifier, PeminjamanListState>(
  (ref) => PeminjamanListNotifier(
    ref.read(peminjamanRepositoryProvider),
    isAdminView: true,
  ),
);

/// Peminjaman detail provider
final peminjamanDetailProvider = FutureProvider.family<ApiResponse<Peminjaman>, int>(
  (ref, id) async {
    final repository = ref.read(peminjamanRepositoryProvider);
    return repository.getPeminjamanById(id);
  },
);

/// Available barang for loan
final availableBarangProvider = FutureProvider<List<Barang>>((ref) async {
  final repository = ref.read(peminjamanRepositoryProvider);
  final response = await repository.getAvailableBarang();
  return response.success ? (response.data ?? []) : [];
});

/// Selected barang for loan cart
final selectedBarangForLoanProvider = StateProvider<List<Barang>>((ref) => []);

/// Add barang to loan cart
void addBarangToLoanCart(WidgetRef ref, Barang barang) {
  final current = ref.read(selectedBarangForLoanProvider);
  if (!current.any((b) => b.id == barang.id)) {
    ref.read(selectedBarangForLoanProvider.notifier).state = [...current, barang];
  }
}

/// Remove barang from loan cart
void removeBarangFromLoanCart(WidgetRef ref, int barangId) {
  final current = ref.read(selectedBarangForLoanProvider);
  ref.read(selectedBarangForLoanProvider.notifier).state =
      current.where((b) => b.id != barangId).toList();
}

/// Clear loan cart
void clearLoanCart(WidgetRef ref) {
  ref.read(selectedBarangForLoanProvider.notifier).state = [];
}
