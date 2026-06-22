# 🚀 Panduan Setup TRASET - Start in One Click

## Prasyarat

1. **XAMPP** dengan PHP 8.3+ terinstall (C:\xampp)
2. **PostgreSQL** terinstall dan berjalan
3. **Composer** terinstall

---

## Langkah Setup (Sekali saja)

### 1. Buat Database PostgreSQL

Buka **pgAdmin** atau terminal psql, lalu jalankan:

```sql
CREATE DATABASE traset;
```

Atau jalankan script:
```bash
"C:\xampp\pgsql\bin\psql.exe" -U postgres -c "CREATE DATABASE traset;"
```

### 2. Setup Database

Klik **2-Setup-Database.bat**

Script ini akan:
- ✅ Install dependencies (composer install)
- ✅ Running migrations (tabel baru)
- ✅ Seed data contoh (struktur lokasi, user demo)

---

## Menjalankan Aplikasi

### Opsi 1: One-Click Start (Paling Mudah)

1. Klik **1-Start-Server.bat**
2. Tunggu sampai muncul: `Server running at http://localhost:8000`
3. Klik **Buka-TRASET.url** untuk membuka di browser

### Opsi 2: Manual

```bash
cd C:\Users\user\Documents\projek_BMN\website-bmn\Backend
C:\xampp\php\php.exe artisan serve
```

Buka browser: http://localhost:8000/admin

---

## Akun Demo

| Peran | Email | Password |
|-------|-------|----------|
| Super Admin | superadmin@bmn.go.id | password |
| Staff BMN | staff@bmn.go.id | password |
| User (Peminjam) | user@bmn.go.id | password |

---

## Struktur Lokasi yang Diciptakan

Seed akan membuat:
- **Direktorat**: Direktorat Umum
- **Gedung**: Gedung A
- **Lantai**: Lantai 1, Lantai 2
- **Lokasi**: Ruang Server, Ruang Rapat, Ruang Kerja 1
- **Ruangan**: Ruang Server Utama, Ruang Rapat Utama, Ruang Kerja Staff

---

## Fitur Baru

### Dashboard
- Statistik lengkap (Total Barang, Tersedia, Dipinjam, Overdue, dll)
- Chart tren peminjaman (Line)
- Chart distribusi status barang (Doughnut)
- Chart distribusi per kategori (Pie)
- Chart peminjaman per status (Bar)
- Tabel transaksi terbaru (20 item)
- Alert peminjaman overdue
- Alert barang kondisi kritis

### Asset Lifecycle
Status barang baru:
- Pengadaan
- Tersedia
- Dipinjam
- Dalam Perawatan
- Rusak Ringan
- Rusak Berat
- Hilang
- Dihapuskan
- Dimusnahkan

### Struktur Lokasi Extended
Hierarki: Direktorat > Gedung > Lantai > Lokasi > Ruangan

### Stock Opname
- Buat sesi stock opname
- Scan QR Code barang
- Input kondisi saat scan
- Status: Ditemukan / Tidak Ditemukan / Rusak

### Sistem Notifikasi
- Notifikasi pengajuan baru
- Notifikasi jatuh tempo (3 hari sebelum)
- Notifikasi overdue
- Notifikasi persetujuan/penolakan

### Export Excel
- Export data barang
- Export data peminjaman

---

## Troubleshooting

### Error: "PHP not found"
Pastikan XAMPP terinstall di C:\xampp dan PHP sudah ditambahkan ke PATH, atau edit file .bat dan ubah path ke lokasi XAMPP Anda.

### Error: "Database 'traset' not found"
Pastikan database PostgreSQL 'traset' sudah dibuat sebelum running setup.

### Error: "Port 8000 already in use"
Ganti port di start-server.bat, ubah `--port=8000` ke port lain seperti `8080`.

---

## File Penting

| File | Fungsi |
|------|--------|
| `1-Start-Server.bat` | Jalankan server development |
| `2-Setup-Database.bat` | Setup database pertama kali |
| `Buka-TRASET.url` | Buka aplikasi di browser |
| `setup-database.bat` | Setup database (alternatif) |
| `start-server.bat` | Start server (alternatif) |

---

**TRASET** - TRAnsaksi & ASET Management System
© {{ date('Y') }}