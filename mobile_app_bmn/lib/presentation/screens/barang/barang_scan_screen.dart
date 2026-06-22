import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:mobile_scanner/mobile_scanner.dart';
import '../../../core/constants/app_colors.dart';
import '../../../data/repositories/barang_repository.dart';
import '../../providers/barang_provider.dart';
import '../../widgets/common_widgets.dart';
import 'barang_detail_screen.dart';

/// QR Scanner screen for barang
class BarangScanScreen extends ConsumerStatefulWidget {
  const BarangScanScreen({super.key});

  @override
  ConsumerState<BarangScanScreen> createState() => _BarangScanScreenState();
}

class _BarangScanScreenState extends ConsumerState<BarangScanScreen> {
  MobileScannerController? _controller;
  bool _isScanning = true;
  String? _lastScannedCode;

  @override
  void initState() {
    super.initState();
    _controller = MobileScannerController(
      detectionSpeed: DetectionSpeed.normal,
      facing: CameraFacing.back,
    );
  }

  @override
  void dispose() {
    _controller?.dispose();
    super.dispose();
  }

  Future<void> _onDetect(BarcodeCapture capture) async {
    if (!_isScanning) return;

    final List<Barcode> barcodes = capture.barcodes;
    for (final barcode in barcodes) {
      final code = barcode.rawValue;
      if (code != null && code != _lastScannedCode) {
        _lastScannedCode = code;
        setState(() => _isScanning = false);
        await _controller?.stop();

        if (mounted) {
          await _processScannedCode(code);
        }

        await Future.delayed(const Duration(seconds: 2));
        if (mounted) {
          setState(() => _isScanning = true);
          _controller?.start();
        }
      }
    }
  }

  Future<void> _processScannedCode(String code) async {
    if (mounted) {
      showDialog(
        context: context,
        barrierDismissible: false,
        builder: (context) => const Center(child: CircularProgressIndicator()),
      );
    }

    final response = await ref.read(barangRepositoryProvider).scanBarang(code);

    if (mounted) {
      Navigator.pop(context);
    }

    if (response.success && response.data != null) {
      if (mounted) {
        showDialog(
          context: context,
          builder: (context) => AlertDialog(
            title: const Text('Barang Ditemukan'),
            content: Column(
              mainAxisSize: MainAxisSize.min,
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text('Nama: ${response.data!.namaBarang}'),
                Text('Kode: ${response.data!.kodeBarang}'),
                Text('Status: ${response.data!.status}'),
                Text('Kondisi: ${response.data!.kondisi}'),
              ],
            ),
            actions: [
              TextButton(
                onPressed: () => Navigator.pop(context),
                child: const Text('Tutup'),
              ),
              ElevatedButton(
                onPressed: () {
                  Navigator.pop(context);
                  Navigator.push(
                    context,
                    MaterialPageRoute(
                      builder: (_) => BarangDetailScreen(barang: response.data!),
                    ),
                  );
                },
                child: const Text('Lihat Detail'),
              ),
            ],
          ),
        );
      }
    } else {
      if (mounted) {
        showErrorSnackBar(context, response.message ?? 'Barang tidak ditemukan');
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Scan QR Code'),
        actions: [
          IconButton(
            icon: const Icon(Icons.flash_on),
            onPressed: () => _controller?.toggleTorch(),
          ),
          IconButton(
            icon: const Icon(Icons.flip_camera_ios),
            onPressed: () => _controller?.switchCamera(),
          ),
        ],
      ),
      body: Stack(
        children: [
          MobileScanner(
            controller: _controller,
            onDetect: _onDetect,
          ),
          // Overlay
          Container(
            decoration: BoxDecoration(
              color: Colors.black.withValues(alpha: 0.5),
            ),
            child: Center(
              child: Container(
                width: 250,
                height: 250,
                decoration: BoxDecoration(
                  border: Border.all(color: AppColors.primary, width: 2),
                  borderRadius: BorderRadius.circular(12),
                ),
                child: ClipRRect(
                  borderRadius: BorderRadius.circular(10),
                  child: ColorFiltered(
                    colorFilter: const ColorFilter.mode(
                      Colors.transparent,
                      BlendMode.srcOut,
                    ),
                    child: Container(
                      color: Colors.black,
                    ),
                  ),
                ),
              ),
            ),
          ),
          // Scan area cutout
          Center(
            child: Container(
              width: 250,
              height: 250,
              decoration: BoxDecoration(
                border: Border.all(color: AppColors.primary, width: 3),
                borderRadius: BorderRadius.circular(12),
              ),
            ),
          ),
          // Instructions
          Positioned(
            bottom: 100,
            left: 0,
            right: 0,
            child: Text(
              'Arahkan kamera ke QR Code barang',
              style: Theme.of(context).textTheme.bodyLarge?.copyWith(
                    color: Colors.white,
                  ),
              textAlign: TextAlign.center,
            ),
          ),
        ],
      ),
    );
  }
}
