<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $barang->kode_barang }} — TRASET</title>
    <style>
        :root { --primary:#1d4ed8; --bg:#f1f5f9; --card:#fff; --muted:#64748b; --line:#e2e8f0; --ok:#16a34a; --warn:#d97706; --bad:#dc2626; }
        * { box-sizing:border-box; margin:0; padding:0; }
        body { font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif; background:var(--bg); color:#0f172a; line-height:1.5; padding:16px; }
        .wrap { max-width:560px; margin:0 auto; }
        .brand { text-align:center; padding:12px 0 20px; }
        .brand h1 { font-size:18px; color:var(--primary); letter-spacing:.5px; }
        .brand p { font-size:12px; color:var(--muted); }
        .card { background:var(--card); border:1px solid var(--line); border-radius:16px; padding:20px; margin-bottom:16px; box-shadow:0 1px 3px rgba(0,0,0,.04); }
        .head { display:flex; gap:16px; align-items:center; }
        .qr { flex-shrink:0; width:96px; height:96px; }
        .qr svg { width:100%; height:100%; }
        .title { font-size:18px; font-weight:700; }
        .code { font-family:ui-monospace,monospace; font-size:13px; color:var(--primary); margin-top:2px; }
        .badges { margin-top:10px; display:flex; gap:6px; flex-wrap:wrap; }
        .badge { font-size:11px; font-weight:600; padding:3px 10px; border-radius:999px; }
        .b-ok { background:#dcfce7; color:var(--ok); }
        .b-warn { background:#fef3c7; color:var(--warn); }
        .b-bad { background:#fee2e2; color:var(--bad); }
        .b-info { background:#dbeafe; color:var(--primary); }
        .b-gray { background:#e2e8f0; color:#475569; }
        h2 { font-size:13px; text-transform:uppercase; letter-spacing:.5px; color:var(--muted); margin-bottom:12px; }
        .row { display:flex; justify-content:space-between; padding:8px 0; border-bottom:1px dashed var(--line); font-size:14px; }
        .row:last-child { border-bottom:none; }
        .row .k { color:var(--muted); }
        .row .v { font-weight:600; text-align:right; }
        .timeline { position:relative; padding-left:22px; }
        .timeline::before { content:''; position:absolute; left:6px; top:4px; bottom:4px; width:2px; background:var(--line); }
        .ev { position:relative; padding-bottom:16px; }
        .ev:last-child { padding-bottom:0; }
        .ev::before { content:''; position:absolute; left:-19px; top:3px; width:10px; height:10px; border-radius:50%; background:var(--primary); border:2px solid #fff; }
        .ev .t { font-weight:600; font-size:14px; }
        .ev .d { font-size:12px; color:var(--muted); }
        .ev .m { font-size:13px; margin-top:2px; }
        .empty { color:var(--muted); font-size:13px; font-style:italic; }
        .actions { display:flex; gap:10px; }
        .btn { flex:1; text-align:center; text-decoration:none; padding:11px; border-radius:10px; font-size:14px; font-weight:600; }
        .btn-pri { background:var(--primary); color:#fff; }
        .btn-out { background:#fff; color:var(--primary); border:1px solid var(--primary); }
        .foot { text-align:center; font-size:11px; color:var(--muted); padding:8px 0 20px; }
    </style>
</head>
<body>
@php
    $kondisiClass = ['baik'=>'b-ok','rusak_ringan'=>'b-warn','rusak_berat'=>'b-bad'][$barang->kondisi] ?? 'b-gray';
    $statusClass = ['tersedia'=>'b-ok','dipinjam'=>'b-info','perbaikan'=>'b-warn','hilang'=>'b-bad','dihapuskan'=>'b-gray'][$barang->status] ?? 'b-gray';
@endphp
<div class="wrap">
    <div class="brand">
        <h1>TRASET</h1>
        <p>Sistem Informasi Manajemen Aset dan Peminjaman Barang Milik Negara</p>
    </div>

    <div class="card">
        <div class="head">
            <div class="qr">{!! $qrSvg !!}</div>
            <div>
                <div class="title">{{ $barang->nama }}</div>
                <div class="code">{{ $barang->kode_barang }}</div>
                <div class="badges">
                    <span class="badge {{ $kondisiClass }}">{{ \App\Models\Barang::KONDISI[$barang->kondisi] ?? $barang->kondisi }}</span>
                    <span class="badge {{ $statusClass }}">{{ \App\Models\Barang::STATUS[$barang->status] ?? $barang->status }}</span>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <h2>Detail Barang</h2>
        <div class="row"><span class="k">Kategori</span><span class="v">{{ $barang->kategori->nama ?? '-' }}</span></div>
        <div class="row"><span class="k">Merek</span><span class="v">{{ $barang->merek ?? '-' }}</span></div>
        <div class="row"><span class="k">Nomor Seri</span><span class="v">{{ $barang->nomor_seri ?? '-' }}</span></div>
        <div class="row"><span class="k">Tahun Perolehan</span><span class="v">{{ $barang->tahun_perolehan ?? '-' }}</span></div>
        <div class="row"><span class="k">Lokasi</span><span class="v">{{ $barang->lokasi->nama ?? '-' }}</span></div>
        <div class="row"><span class="k">Ruangan</span><span class="v">{{ $barang->ruangan->nama ?? '-' }}</span></div>
    </div>

    <div class="card">
        <h2>Histori Barang</h2>
        @if($barang->histori->isEmpty())
            <p class="empty">Belum ada riwayat untuk barang ini.</p>
        @else
            <div class="timeline">
                @foreach($barang->histori as $h)
                    <div class="ev">
                        <div class="t">{{ $h->judul }}</div>
                        <div class="d">{{ optional($h->terjadi_pada)->format('d M Y H:i') }} @if($h->user) · {{ $h->user->name }} @endif</div>
                        @if($h->deskripsi)<div class="m">{{ $h->deskripsi }}</div>@endif
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <div class="card">
        <div class="actions">
            <a class="btn btn-out" href="{{ route('barang.qr.download', $barang->kode_barang) }}">Unduh QR</a>
            <a class="btn btn-pri" href="/admin">Masuk Sistem</a>
        </div>
    </div>

    <div class="foot">© {{ date('Y') }} TRASET · Dipindai pada {{ now()->format('d M Y H:i') }}</div>
</div>
</body>
</html>
