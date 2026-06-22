<!DOCTYPE html>
<html lang="id"><head><meta charset="utf-8">@include('pdf._style')</head>
<body>
@include('pdf._kop')

<div class="doc-title">
    <h3>Surat Pernyataan Peminjaman Barang Milik Negara</h3>
    <p>Nomor: SP/{{ $peminjaman->nomor_peminjaman }}/BMN/PP.1/{{ now()->format('Y') }}</p>
</div>

<p class="mt">Yang bertanda tangan di bawah ini:</p>

<table class="meta">
    <tr><td class="label">Nama</td><td class="sep">:</td><td>{{ $peminjaman->peminjam->name ?? '-' }}</td></tr>
    <tr><td class="label">NIP</td><td class="sep">:</td><td>{{ $peminjaman->peminjam->nip ?? '-' }}</td></tr>
    <tr><td class="label">Pangkat/Gol.</td><td class="sep">:</td><td>-</td></tr>
    <tr><td class="label">Unit Kerja</td><td class="sep">:</td><td>{{ $peminjaman->unitKerja->nama ?? ($peminjaman->peminjam->unitKerja->nama ?? '-') }}</td></tr>
</table>

<p class="mt">Dengan ini menyatakan bahwa dalam rangka peminjaman Barang Milik Negara, akan:</p>
<ol style="font-size:11px; line-height:1.8; margin:8px 0;">
    <li>Menggunakan BMN dalam rangka melaksanakan tugas dan fungsi;</li>
    <li>Menjaga dan memelihara BMN;</li>
    <li>Melaporkan kepada atasan langsung jika BMN rusak/hilang;</li>
    <li>Memperbaiki jika BMN yang dipinjam rusak selama jangka waktu peminjaman;</li>
    <li>Mengganti jika BMN yang dipinjam hilang selama jangka waktu peminjaman; dan</li>
    <li>Mengembalikan BMN yang dipinjam sesuai dengan kondisi semula apabila ditugaskan ke unit kerja lain (mutasi)/jangka waktu peminjaman BMN berakhir.</li>
</ol>

<p class="mt mb"><strong>Dengan perincian data barang:</strong></p>
<table class="data">
    <thead><tr><th style="width:24px;">No</th><th>Nama Barang</th><th>Merek dan Tipe</th><th>NUP</th><th>Jumlah</th><th>Kondisi</th></tr></thead>
    <tbody>
    @php $totalUnit = 0; @endphp
    @forelse($peminjaman->details as $i => $d)
        <tr>
            <td>{{ $i+1 }}</td>
            <td>{{ $d->barang->nama ?? '-' }}</td>
            <td>{{ trim(($d->barang->merek ?? '') . ' ' . ($d->barang->nomor_seri ? '/ '.$d->barang->nomor_seri : '')) ?: '-' }}</td>
            <td>{{ substr($d->barang->kode_barang ?? '', -6) }}</td>
            <td>1 unit</td>
            <td>{{ \App\Models\Barang::KONDISI[$d->kondisi_pinjam] ?? $d->kondisi_pinjam }}</td>
        </tr>
        @php $totalUnit++; @endphp
    @empty
        <tr><td colspan="6" style="text-align:center;">Tidak ada barang.</td></tr>
    @endforelse
    <tr style="font-weight:bold; background:#e8edf5;">
        <td colspan="4" style="text-align:right;">Total</td>
        <td>{{ $totalUnit }} unit</td>
        <td></td>
    </tr>
    </tbody>
</table>

<p class="mt small">Demikian pernyataan ini kami buat dengan sebenar-benarnya untuk dipergunakan sebagaimana mestinya.</p>

<table class="ttd">
    <tr>
        <td></td>
        <td>Jakarta, {{ now()->translatedFormat('d F Y') }}</td>
        <td></td>
    </tr>
    <tr>
        <td>Menyetujui,<br>Atasan Langsung</td>
        <td></td>
        <td>Peminjam BMN</td>
    </tr>
    <tr><td class="space"></td><td></td><td class="space"></td></tr>
    <tr>
        <td class="name">{{ $peminjaman->approvedAtasan->name ?? '(.........................)' }}</td>
        <td></td>
        <td class="name">{{ $peminjaman->peminjam->name ?? '(.........................)' }}</td>
    </tr>
    <tr>
        <td class="small">NIP. {{ $peminjaman->approvedAtasan->nip ?? '-' }}</td>
        <td></td>
        <td class="small">NIP. {{ $peminjaman->peminjam->nip ?? '-' }}</td>
    </tr>
</table>

</body></html>
