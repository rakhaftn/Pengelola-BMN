<!DOCTYPE html>
<html lang="id"><head><meta charset="utf-8">@include('pdf._style')</head>
<body>
@include('pdf._kop')
@php $p = $pengembalian->peminjaman; @endphp

<div class="doc-title">
    <h3>Berita Acara Pengembalian Barang</h3>
    <p>Nomor: {{ $pengembalian->nomor_pengembalian }}</p>
</div>

<p class="mt">Pada hari ini, {{ optional($pengembalian->tanggal_pengembalian)->translatedFormat('l, d F Y') }}, telah dilakukan pengembalian Barang Milik Negara atas peminjaman nomor <strong>{{ $p->nomor_peminjaman ?? '-' }}</strong>, dengan rincian sebagai berikut:</p>

<table class="meta mt mb">
    <tr><td class="label">Peminjam</td><td class="sep">:</td><td>{{ $p->peminjam->name ?? '-' }}</td></tr>
    <tr><td class="label">NIP</td><td class="sep">:</td><td>{{ $p->peminjam->nip ?? '-' }}</td></tr>
    <tr><td class="label">Tanggal Pengembalian</td><td class="sep">:</td><td>{{ optional($pengembalian->tanggal_pengembalian)->format('d F Y') }}</td></tr>
    <tr><td class="label">Kondisi Barang</td><td class="sep">:</td><td>{{ \App\Models\Barang::KONDISI[$pengembalian->kondisi_barang] ?? $pengembalian->kondisi_barang }}</td></tr>
    <tr><td class="label">Diterima Oleh</td><td class="sep">:</td><td>{{ $pengembalian->diterimaOleh->name ?? '-' }}</td></tr>
    <tr><td class="label">Catatan</td><td class="sep">:</td><td>{{ $pengembalian->catatan ?? '-' }}</td></tr>
</table>

<p><strong>Barang yang dikembalikan:</strong></p>
<table class="data">
    <thead><tr><th style="width:24px;">No</th><th>Kode Barang</th><th>Nama Barang</th><th>Kondisi Pinjam</th><th>Kondisi Kembali</th></tr></thead>
    <tbody>
    @forelse(optional($p)->details ?? [] as $i => $d)
        <tr>
            <td>{{ $i+1 }}</td>
            <td>{{ $d->barang->kode_barang ?? '-' }}</td>
            <td>{{ $d->barang->nama ?? '-' }}</td>
            <td>{{ \App\Models\Barang::KONDISI[$d->kondisi_pinjam] ?? $d->kondisi_pinjam }}</td>
            <td>{{ \App\Models\Barang::KONDISI[$d->kondisi_kembali] ?? ($d->kondisi_kembali ?? '-') }}</td>
        </tr>
    @empty
        <tr><td colspan="5" style="text-align:center;">Tidak ada barang.</td></tr>
    @endforelse
    </tbody>
</table>

<p class="mt small">Demikian Berita Acara Pengembalian ini dibuat dengan sebenarnya.</p>

<table class="ttd">
    <tr><td>Yang Mengembalikan</td><td></td><td>Yang Menerima<br>(Petugas BMN)</td></tr>
    <tr><td class="space"></td><td></td><td class="space"></td></tr>
    <tr>
        <td class="name">{{ $p->peminjam->name ?? '(................)' }}</td><td></td>
        <td class="name">{{ $pengembalian->diterimaOleh->name ?? '(................)' }}</td>
    </tr>
    <tr>
        <td class="small">NIP. {{ $p->peminjam->nip ?? '-' }}</td><td></td>
        <td class="small">NIP. {{ $pengembalian->diterimaOleh->nip ?? '-' }}</td>
    </tr>
</table>

</body></html>
