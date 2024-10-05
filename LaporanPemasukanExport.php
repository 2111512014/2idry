<?php

namespace App\Exports;

use App\Models\Laporan;
use App\Models\transaksi;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\FromView;

class LaporanPemasukanExport implements FromView
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function view(): View
    {
      $data = Laporan::where('jenis_laporan', 'pemasukan')->whereMonth('tgl', Carbon::now()->month)->get();
      return view(
        'modul_admin.laporan.laporan-pemasukan.excelExport',
        [
          'data'  => $data
        ]
      );
    }
}
