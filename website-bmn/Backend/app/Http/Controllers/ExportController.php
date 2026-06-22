<?php

namespace App\Http\Controllers;

use App\Exports\BarangExport;
use App\Exports\PeminjamanExport;
use Illuminate\Http\Request;

class ExportController extends Controller
{
    public function barang(BarangExport $export)
    {
        return $export->export();
    }

    public function peminjaman(PeminjamanExport $export)
    {
        return $export->export();
    }
}