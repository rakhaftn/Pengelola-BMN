<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stock Opname - {{ $stockOpname->nomor_opname }} | TRASET</title>
    <style>
        :root { --primary: #1d4ed8; --bg: #f1f5f9; --card: #fff; --muted: #64748b; --line: #e2e8f0; --ok: #16a34a; --warn: #d97706; --bad: #dc2626; }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background: var(--bg); color: #0f172a; line-height: 1.5; padding: 16px; }
        .wrap { max-width: 600px; margin: 0 auto; }
        .brand { text-align: center; padding: 12px 0 20px; }
        .brand h1 { font-size: 18px; color: var(--primary); }
        .brand p { font-size: 12px; color: var(--muted); }
        .card { background: var(--card); border: 1px solid var(--line); border-radius: 12px; padding: 16px; margin-bottom: 16px; }
        h2 { font-size: 14px; font-weight: 600; margin-bottom: 12px; color: var(--muted); text-transform: uppercase; }
        .info { display: grid; grid-template-columns: 1fr 1fr; gap: 8px; font-size: 14px; }
        .info span:first-child { color: var(--muted); }
        .info span:last-child { font-weight: 600; }
        .form-scan { margin-top: 16px; }
        input, select, textarea { width: 100%; padding: 12px; border: 1px solid var(--line); border-radius: 8px; font-size: 16px; margin-bottom: 12px; }
        button { width: 100%; padding: 14px; background: var(--primary); color: #fff; border: none; border-radius: 8px; font-size: 16px; font-weight: 600; cursor: pointer; }
        button:hover { background: #1e40af; }
        .flash { padding: 12px; border-radius: 8px; margin-bottom: 16px; }
        .flash-success { background: #dcfce7; color: var(--ok); }
        .flash-error { background: #fee2e2; color: var(--bad); }
        .flash-warning { background: #fef3c7; color: var(--warn); }
        table { width: 100%; border-collapse: collapse; font-size: 13px; }
        th, td { padding: 8px; text-align: left; border-bottom: 1px solid var(--line); }
        th { color: var(--muted); font-weight: 600; }
        .badge { display: inline-block; padding: 2px 8px; border-radius: 999px; font-size: 11px; font-weight: 600; }
        .badge-ditemukan { background: #dcfce7; color: var(--ok); }
        .badge-tidak_ditemukan { background: #fee2e2; color: var(--bad); }
        .badge-rusak { background: #fef3c7; color: var(--warn); }
        .btn-back { display: inline-block; padding: 10px 20px; background: var(--primary); color: #fff; text-decoration: none; border-radius: 8px; font-size: 14px; }
    </style>
</head>
<body>
<div class="wrap">
    <div class="brand">
        <h1>TRASET - Stock Opname</h1>
        <p>Digital Asset Tracking System</p>
    </div>

    @if(session('success'))
        <div class="flash flash-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="flash flash-error">{{ session('error') }}</div>
    @endif
    @if(session('warning'))
        <div class="flash flash-warning">{{ session('warning') }}</div>
    @endif

    <div class="card">
        <h2>Info Stock Opname</h2>
        <div class="info">
            <span>Nomor:</span><span>{{ $stockOpname->nomor_opname }}</span>
            <span>Tanggal:</span><span>{{ $stockOpname->tanggal_opname->format('d M Y') }}</span>
            <span>Status:</span><span>{{ $stockOpname->status }}</span>
            <span>Total Scan:</span><span>{{ $details->count() }} barang</span>
        </div>
    </div>

    @if($stockOpname->status === 'berlangsung')
    <div class="card">
        <h2>Scan QR Code</h2>
        <form action="{{ route('stock-opname.scan.process', $stockOpname) }}" method="POST" class="form-scan">
            @csrf
            <input type="text" name="kode_barang" placeholder="Scan atau ketik Kode Barang (contoh: BMN-2026-000001)" required autofocus>
            <select name="status" required>
                <option value="">-- Status --</option>
                <option value="ditemukan">Ditemukan</option>
                <option value="tidak_ditemukan">Tidak Ditemukan</option>
                <option value="rusak">Rusak</option>
            </select>
            <select name="kondisi">
                <option value="">-- Kondisi (opsional) --</option>
                <option value="baik">Baik</option>
                <option value="rusak_ringan">Rusak Ringan</option>
                <option value="rusak_berat">Rusak Berat</option>
            </select>
            <textarea name="catatan" placeholder="Catatan (opsional)" rows="2"></textarea>
            <button type="submit">Simpan Scan</button>
        </form>
    </div>
    @endif

    <div class="card">
        <h2>Barang yang Sudah Discanned ({{ $details->count() }})</h2>
        @if($details->isEmpty())
            <p style="color: var(--muted); font-style: italic;">Belum ada barang yang discan.</p>
        @else
        <table>
            <thead>
                <tr>
                    <th>Kode</th>
                    <th>Nama</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($details as $detail)
                <tr>
                    <td>{{ $detail->barang->kode_barang }}</td>
                    <td>{{ $detail->barang->nama }}</td>
                    <td><span class="badge badge-{{ $detail->status }}">{{ $detail->status }}</span></td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif
    </div>

    <div style="text-align: center;">
        <a href="/admin/stock-opname" class="btn-back">Kembali ke Daftar</a>
    </div>
</div>
</body>
</html>