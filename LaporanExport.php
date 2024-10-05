<?php

namespace App\Exports;

use App\Models\CabangLaundry;
use App\Models\transaksi;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\FromView;

class LaporanExport implements FromView
{
    private $data;
    private $totalBerat;
    private $totalPending;
    private $totalPemasukan;
    private $totalPiutang;
    function __construct($a,$b,$c,$d,$e)
    {
      $this->data = $a;
      $this->totalBerat = $b;
      $this->totalPending = $c;
      $this->totalPemasukan = $d;
      $this->totalPiutang = $e;
    }

    /**
    * @return \Illuminate\Support\Collection
    */
    public function view(): View
    {
      return view(
        'karyawan.laporan.excelExport',
        [
          'data'  => $this->data,
          'totalBerat' => $this->totalBerat,
          'totalPending' => $this->totalPending,
          'totalPemasukan' => $this->totalPemasukan,
          'totalPiutang' => $this->totalPiutang,
        ]
      );
    }
}
