<?php

namespace App\Exports;

use App\Models\Laporan;
use App\Models\transaksi;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\FromView;

class LaporanPengeluaranExport implements FromView
{
    private $pengeluaranBulanan;
    private $pengeluaranBulanSebelumnya;
    private $pemasukanBulanSebelumnya;
    private $laporan;
    private $pemasukanCash;
    private $totalPemasukan;
    private $saldoAkhir;
    function __construct($pengeluaranBulanan, $pengeluaranBulanSebelumnya, $pemasukanBulanSebelumnya, $laporan, $pemasukanCash, $totalPemasukan, $saldoAkhir) {
      $this->pengeluaranBulanan = $pengeluaranBulanan;
      $this->pengeluaranBulanSebelumnya = $pengeluaranBulanSebelumnya;
      $this->pemasukanBulanSebelumnya = $pemasukanBulanSebelumnya;
      $this->laporan = $laporan;
      $this->pemasukanCash = $pemasukanCash;
      $this->totalPemasukan = $totalPemasukan;
      $this->saldoAkhir = $saldoAkhir;
    }
    /**
    * @return \Illuminate\Support\Collection
    */
    public function view(): View
    {
      return view(
        'modul_admin.laporan.laporan-pengeluaran.excelExport',
        [
          'data'  => $this->laporan,
          'pemasukanCash' => $this->pemasukanCash,
          'pengeluaranBulanan' => $this->pengeluaranBulanan,
          'pengeluaranBulanSebelumnya' => $this->pengeluaranBulanSebelumnya,
          'pemasukanBulanSebelumnya' => $this->pemasukanBulanSebelumnya,
          'totalPemasukan' => $this->totalPemasukan,
          'totalPengeluaran' => $this->pengeluaranBulanan,
          'saldoAkhir' => $this->saldoAkhir,
        ]
      );
    }
}
