<?php

namespace App\Http\Controllers\Customer;

use App\Models\transaksi;
use App\Models\User;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class TransaksiController extends Controller
{
    public function index()
    {
        $sisaUang = User::where('id',Auth::id())->sum('sisa_uang');
        $totalLaundry = transaksi::with('cuciKiloan', 'itemSatuan')->where('customer_id',Auth::id())->count();
        $totalLaundryKg = transaksi::with('cuciKiloan', 'itemSatuan')->where('customer_id',Auth::id())->sum('kg');
        $totalLaundryKgBulanIni = transaksi::with('cuciKiloan', 'itemSatuan')->where('customer_id',Auth::id())->where('tahun', date('Y'))
                ->where('bulan', ltrim(date('m'), '0'))->sum('kg');
        $totalLaundryKgBulanLalu = transaksi::with('cuciKiloan', 'itemSatuan')->where('customer_id',Auth::id())->where('bulan', date("M", strtotime("-1 month")))->sum('kg');
      
        $transaksi = transaksi::with('cuciKiloan', 'itemSatuan')->where('customer_id',Auth::id())->orderBy('tgl_transaksi','desc')->get();
        return view('customer.transaksi.index',\compact('totalLaundry','totalLaundryKg','transaksi', 'sisaUang', 'totalLaundryKgBulanIni', 'totalLaundryKgBulanLalu', ));
    }
}
