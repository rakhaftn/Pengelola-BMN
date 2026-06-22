<!DOCTYPE html>
<html lang="id"><head><meta charset="utf-8">@include('pdf._style')</head>
<body>
@include('pdf._kop')

<div class="doc-title">
    <h3>Formulir Peminjaman Barang Milik Negara</h3>
    <p>Nomor: {{ $peminjaman->nomor_peminjaman }}</p>
</div>

<table class="meta mt">
    <tr><td class="label">Nama Peminjam</td><td class="sep">:</td><td>{{ $peminjaman->peminjam->name ?? '-' }}</td></tr>
    <tr><td class="label">NIP</td><td class="sep">:</td><td>{{ $peminjaman->peminjam->nip ?? '-' }}</td></tr>
    <tr><td class="label">Jabatan</td><td class="sep">:</td><td>{{ $peminjaman->peminjam->jabatan ?? '-' }}</td></tr>
    <tr><td class="label">Unit Kerja</td><td class="sep">:</td><td>{{ $peminjaman->unitKerja->nama ?? ($peminjaman->peminjam->unitKerja->nama ?? '-') }}</td></tr>
    <tr><td class="label">Tanggal Pinjam</td><td class="sep">:</td><td>{{ optional($peminjaman->tanggal_pinjam)->format('d F Y') }}</td></tr>
    <tr><td class="label">Rencana Kembali</td><td class="sep">:</td><td>{{ optional($peminjaman->tanggal_kembali_rencana)->format('d F Y') }}</td></tr>
    <tr><td class="label">Keperluan</td><td class="sep">:</td><td>{{ $peminjaman->keperluan ?? $peminjaman->tujuan ?? '-' }}</td></tr>
</table>

<p class="mt mb"><strong>Daftar Barang yang Dipinjam:</strong></p>
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

<p class="mt small">Dengan ini saya menyatakan akan menjaga dan bertanggung jawab penuh atas barang yang dipinjam serta mengembalikan tepat waktu dalam kondisi baik.</p>

<table class="ttd">
    <tr>
        <td>Menyetujui,<br>Atasan Langsung</td>
        <td>Petugas BMN</td>
        <td>Peminjam</td>
    </tr>
    <tr><td class="space"></td><td class="space"></td><td class="space"></td></tr>
    <tr>
        <td class="name">{{ $peminjaman->approvedAtasan->name ?? '(.........................)' }}</td>
        <td class="name">{{ $peminjaman->approvedPetugas->name ?? '(.........................)' }}</td>
        <td class="name">{{ $peminjaman->peminjam->name ?? '(.........................)' }}</td>
    </tr>
    <tr>
        <td class="small">NIP. {{ $peminjaman->approvedAtasan->nip ?? '-' }}</td>
        <td class="small">NIP. {{ $peminjaman->approvedPetugas->nip ?? '-' }}</td>
        <td class="small">NIP. {{ $peminjaman->peminjam->nip ?? '-' }}</td>
    </tr>
</table>

</body></html>
