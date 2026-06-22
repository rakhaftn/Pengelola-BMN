<!DOCTYPE html>
<html lang="id"><head><meta charset="utf-8">@include('pdf._style')</head>
<body>
@include('pdf._kop')

<div class="doc-title">
    <h3>Berita Acara Serah Terima Barang</h3>
    <p>Nomor: BAST/{{ $peminjaman->nomor_peminjaman }}</p>
</div>

<p class="mt">Pada hari ini, {{ now()->translatedFormat('l, d F Y') }}, yang bertanda tangan di bawah ini telah melakukan serah terima Barang Milik Negara dalam rangka peminjaman, dengan rincian sebagai berikut:</p>

<table class="meta mt mb">
    <tr><td class="label">PIHAK PERTAMA (Yang Menyerahkan)</td><td class="sep"></td><td></td></tr>
    <tr><td class="label">Nama</td><td class="sep">:</td><td>{{ $peminjaman->approvedPetugas->name ?? 'Petugas BMN' }}</td></tr>
    <tr><td class="label">Jabatan</td><td class="sep">:</td><td>{{ $peminjaman->approvedPetugas->jabatan ?? 'Petugas BMN' }}</td></tr>
    <tr><td class="label">&nbsp;</td><td></td><td></td></tr>
    <tr><td class="label">PIHAK KEDUA (Yang Menerima)</td><td class="sep"></td><td></td></tr>
    <tr><td class="label">Nama</td><td class="sep">:</td><td>{{ $peminjaman->peminjam->name ?? '-' }}</td></tr>
    <tr><td class="label">NIP</td><td class="sep">:</td><td>{{ $peminjaman->peminjam->nip ?? '-' }}</td></tr>
    <tr><td class="label">Unit Kerja</td><td class="sep">:</td><td>{{ $peminjaman->peminjam->unitKerja->nama ?? '-' }}</td></tr>
</table>

<p><strong>Barang yang diserahterimakan:</strong></p>
<table class="data">
    <thead><tr><th style="width:24px;">No</th><th>Kode Barang</th><th>Nama Barang</th><th>Merek/Seri</th><th>Kondisi</th></tr></thead>
    <tbody>
    @forelse($peminjaman->details as $i => $d)
        <tr>
            <td>{{ $i+1 }}</td>
            <td>{{ $d->barang->kode_barang ?? '-' }}</td>
            <td>{{ $d->barang->nama ?? '-' }}</td>
            <td>{{ trim(($d->barang->merek ?? '') . ' ' . ($d->barang->nomor_seri ? '/ '.$d->barang->nomor_seri : '')) ?: '-' }}</td>
            <td>{{ \App\Models\Barang::KONDISI[$d->kondisi_pinjam] ?? $d->kondisi_pinjam }}</td>
        </tr>
    @empty
        <tr><td colspan="5" style="text-align:center;">Tidak ada barang.</td></tr>
    @endforelse
    </tbody>
</table>

<p class="mt small">Demikian Berita Acara Serah Terima ini dibuat dengan sebenarnya untuk dapat dipergunakan sebagaimana mestinya.</p>

<table class="ttd">
    <tr><td>PIHAK KEDUA<br>(Penerima)</td><td></td><td>PIHAK PERTAMA<br>(Petugas BMN)</td></tr>
    <tr><td class="space"></td><td></td><td class="space"></td></tr>
    <tr>
        <td class="name">{{ $peminjaman->peminjam->name ?? '(................)' }}</td>
        <td></td>
        <td class="name">{{ $peminjaman->approvedPetugas->name ?? '(................)' }}</td>
    </tr>
    <tr>
        <td class="small">NIP. {{ $peminjaman->peminjam->nip ?? '-' }}</td><td></td>
        <td class="small">NIP. {{ $peminjaman->approvedPetugas->nip ?? '-' }}</td>
    </tr>
</table>

</body></html>
