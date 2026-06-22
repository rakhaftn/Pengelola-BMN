# TRASET - TRAnsaksi & ASET Management

Sistem manajemen aset dan transaksi BMN dengan struktur terpisah untuk kemudahan maintenance.

## Struktur Folder

```
website-bmn/
├── Backend/          # Laravel API & server-side (PHP)
│   ├── app/          # Controllers, Models, Services
│   ├── routes/       # API & web routes
│   ├── database/     # Migrations & seeders
│   ├── resources/views/  # Blade templates (PDF, SPA shell)
│   └── public/       # Entry point web server
│
├── Frontend/         # React admin panel (JavaScript/TypeScript)
│   ├── js/           # React components, pages, hooks
│   ├── css/          # Tailwind CSS
│   └── package.json  # Dependencies frontend
│
├── 1-Start-Server.bat    # Jalankan backend Laravel
├── 2-Setup-Database.bat  # Setup database pertama kali
├── 4-Start-Frontend.bat  # Jalankan Vite dev server (development)
└── QUICK-START.md        # Panduan lengkap
```

## Quick Start

1. Setup database: jalankan `2-Setup-Database.bat`
2. Jalankan backend: `1-Start-Server.bat`
3. Buka browser: http://localhost:8000

Untuk development frontend dengan hot reload, jalankan juga `4-Start-Frontend.bat`.

## Akun Demo

| Peran | Email | Password |
|-------|-------|----------|
| Super Admin | superadmin@bmn.go.id | password |
| Staff BMN | staff@bmn.go.id | password |
| User | user@bmn.go.id | password |

Lihat `QUICK-START.md` untuk panduan lengkap.
