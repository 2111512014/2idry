<?php

namespace App\Http\Controllers\Karyawan;

use App\Exports\LaporanExport;
use App\Http\Controllers\Controller;
use App\Models\{CabangLaundry, transaksi};
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;

class LaporanController extends Controller
{
    //Halaman Laporan
    public function laporan()
    {
      $laporan = transaksi::where('id_cabang_laundry', Auth::user()->cabangLaundry->id)->get();
      return view('karyawan.laporan.index', compact('laporan'));
    }

    // Export Excel
    public function exportExcel(Request $request)
    {
      $bulan = $request->input('bulan') ? $request->input('bulan') : ltrim(date('m'), '0');
      $tahun = $request->input('tahun') ? $request->input('tahun') : date('Y');
      $status_order = $request->input('status_order');
      $filter = CabangLaundry::where('id_admin', Auth::id())->pluck('id')->all();
      if (Auth::user()->auth == 'Admin') {
        $data = transaksi::with('cuciKiloan','itemSatuan')->where('tahun', $tahun)
        ->where('bulan', $bulan)->whereIN('id_cabang_laundry', $filter)
        ->when($status_order != NULL, function($q) use ($status_order){
            return $q->where('status_order', $status_order);
        })
        ->get();

        $totalBerat = transaksi::with('cuciKiloan','itemSatuan')->where('tahun', $tahun)
        ->where('bulan', $bulan)->whereIN('id_cabang_laundry', $filter)
        ->when($status_order != NULL, function($q) use ($status_order){
            return $q->where('status_order', $status_order);
        })->sum('kg');

        $totalPending = transaksi::with('cuciKiloan','itemSatuan')->where('status_payment', 'Pending')->where('tahun', $tahun)
        ->where('bulan', $bulan)->whereIN('id_cabang_laundry', $filter)
        ->when($status_order != NULL, function($q) use ($status_order){
            return $q->where('status_order', $status_order);
        })
        ->sum('kg');

        $totalPemasukan = transaksi::with('cuciKiloan','itemSatuan')->where('tahun', $tahun)
        ->where('bulan', $bulan)->whereIN('id_cabang_laundry', $filter)
        ->when($status_order != NULL, function($q) use ($status_order){
            return $q->where('status_order', $status_order);
        })->sum('harga_akhir');

        $totalPiutang = transaksi::with('customers')->where('tahun', $tahun)
        ->where('bulan', $bulan)->whereIN('id_cabang_laundry', $filter)
        ->when($status_order != NULL, function($q) use ($status_order){
            return $q->where('status_order', $status_order);
        })
        ->get();
        
        $sum = 0;
        foreach ($totalPiutang as $key => $item) {
            $sum += $item->customers->sisa_uang;
        }
        $totalPiutang = $sum;
      }else{
        $data = transaksi::where('id_cabang_laundry',Auth::user()->cabangLaundry->id)->get();
      }
      return Excel::download(new LaporanExport($data,$totalBerat, $totalPending, $totalPemasukan, $totalPiutang), 'Laporan Bulanan ' . Carbon::create()->month($bulan)->format('F') . '.xlsx');
    }
}
