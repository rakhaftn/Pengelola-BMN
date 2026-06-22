<!DOCTYPE html>
<html lang="id"><head><meta charset="utf-8">@include('pdf._style')</head>
<body>
@include('pdf._kop')

<div class="doc-title">
    <h3>Surat Pengembalian Peminjaman Barang Milik Negara</h3>
    <p>Nomor: {{ $pengembalian->nomor_pengembalian ?? 'SP-' . ($pengembalian->peminjaman->nomor_peminjaman ?? 'N/A') }}/{{ now()->format('Y') }}</p>
</div>

<p class="mt">Yang bertanda tangan di bawah ini:</p>

<table class="meta">
    <tr><td class="label">Nama</td><td class="sep">:</td><td>{{ $pengembalian->peminjaman->peminjam->name ?? '-' }}</td></tr>
    <tr><td class="label">NIP</td><td class="sep">:</td><td>{{ $pengembalian->peminjaman->peminjam->nip ?? '-' }}</td></tr>
    <tr><td class="label">Unit Kerja</td><td class="sep">:</td><td>{{ $pengembalian->peminjaman->peminjam->unitKerja->nama ?? '-' }}</td></tr>
    <tr><td class="label">Tanggal Pinjam</td><td class="sep">:</td><td>{{ optional($pengembalian->peminjaman->tanggal_pinjam)->format('d F Y') }}</td></tr>
    <tr><td class="label">Tanggal Kembali</td><td class="sep">:</td><td>{{ optional($pengembalian->tanggal_pengembalian)->format('d F Y') }}</td></tr>
    <tr><td class="label">Nomor Peminjaman</td><td class="sep">:</td><td>{{ $pengembalian->peminjaman->nomor_peminjaman ?? '-' }}</td></tr>
</table>

<p class="mt">Dengan ini menyatakan telah mengembalikan Barang Milik Negara yang sebelumnya dipinjam dengan kondisi sebagai berikut:</p>

<p class="mt mb"><strong>Daftar Barang yang Dikembalikan:</strong></p>
<table class="data">
    <thead><tr><th style="width:24px;">No</th><th>Kode Barang</th><th>Nama Barang</th><th>Merek/Tipe</th><th>Kondisi Saat Pinjam</th><th>Kondisi Saat Kembali</th></tr></thead>
    <tbody>
    @forelse($pengembalian->peminjaman->details as $i => $d)
        <tr>
            <td>{{ $i+1 }}</td>
            <td>{{ $d->barang->kode_barang ?? '-' }}</td>
            <td>{{ $d->barang->nama ?? '-' }}</td>
            <td>{{ trim(($d->barang->merek ?? '') . ' ' . ($d->barang->nomor_seri ? '/ '.$d->barang->nomor_seri : '')) ?: '-' }}</td>
            <td>{{ \App\Models\Barang::KONDISI[$d->kondisi_pinjam] ?? $d->kondisi_pinjam }}</td>
            <td>{{ \App\Models\Barang::KONDISI[$d->kondisi_kembali] ?? ($pengembalian->kondisi_barang ?? '-') }}</td>
        </tr>
    @empty
        <tr><td colspan="6" style="text-align:center;">Tidak ada data barang.</td></tr>
    @endforelse
    </tbody>
</table>

@if($pengembalian->catatan)
<p class="mt"><strong>Catatan:</strong> {{ $pengembalian->catatan }}</p>
@endif

@if($pengembalian->ada_kerusakan)
<p class="mt small" style="color:#c00;"><strong>Perhatian:</strong> Terdapat kerusakan pada barang yang dikembalikan. Silakan hubungi petugas BMN untuk proses lebih lanjut.</p>
@endif

<p class="mt small">Demikian surat pengembalian ini dibuat dengan sebenarnya untuk dapat dipergunakan sebagaimana mestinya.</p>

<table class="ttd">
    <tr>
        <td></td>
        <td>Jakarta, {{ optional($pengembalian->tanggal_pengembalian)->format('d F Y') ?? now()->translatedFormat('d F Y') }}</td>
        <td></td>
    </tr>
    <tr>
        <td>Petugas BMN<br>(Penerima)</td>
        <td></td>
        <td>Peminjam<br>(Penyerah)</td>
    </tr>
    <tr><td class="space"></td><td></td><td class="space"></td></tr>
    <tr>
        <td class="name">{{ $pengembalian->diterimaOleh->name ?? '(.........................)' }}</td>
        <td></td>
        <td class="name">{{ $pengembalian->peminjaman->peminjam->name ?? '(.........................)' }}</td>
    </tr>
    <tr>
        <td class="small">NIP. {{ $pengembalian->diterimaOleh->nip ?? '-' }}</td>
        <td></td>
        <td class="small">NIP. {{ $pengembalian->peminjaman->peminjam->nip ?? '-' }}</td>
    </tr>
</table>

</body></html>
