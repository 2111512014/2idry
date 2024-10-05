<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\{CabangLaundry, transaksi,LaundrySetting,User,harga,DataBank};
use App\Http\Requests\HargaRequest;
use DB;
use Auth;
use Session;

class FinanceController extends Controller
{
  // Finance
  public function index()
  {
    $cabang = CabangLaundry::where('id_admin', Auth::id())->pluck('id')->all();
    $chartMonthSalary = DB::table('transaksis')
    ->select('bulan', DB::raw('sum(harga_akhir) AS jml'))
    ->whereYear('created_at','=',date("Y", strtotime(now())))
    ->whereMonth('created_at','=',date("m", strtotime(now())))
    ->whereIN('id_cabang_laundry', $cabang)
    ->groupBy('bulan')
    ->get();

    $bulans = '';
    $batas =  12;
    $chartMonth = '';
    for($_i=1; $_i <= $batas; $_i++){
        $bulans = $bulans . (string)$_i . ',';
        $_check = false;
        foreach($chartMonthSalary as $_data){
            if((int)@$_data->bulan === $_i){
                $chartMonth = $chartMonth . (string)$_data->jml . ',';
                $_check = true;
            }
        }
        if(!$_check){
            $chartMonth = $chartMonth . '0,';
        }
    }

    $incomeAll = transaksi::where('status_payment','Success')->whereIN('id_cabang_laundry', $cabang)->sum('harga_akhir');
    $incomeY = transaksi::where('status_payment','Success')->whereIN('id_cabang_laundry', $cabang)->where('tahun',date('Y'))
    ->sum('harga_akhir');

    $incomeM = transaksi::where('status_payment','Success')->where('tahun',date('Y'))
    ->where('bulan', ltrim(date('m'),'0'))->whereIN('id_cabang_laundry', $cabang)->sum('harga_akhir');

    $incomeYOld = transaksi::where('status_payment','Success')->where('tahun',date("Y",strtotime("-1 year")))->whereIN('id_cabang_laundry', $cabang)->sum('harga_akhir');

    $incomeD = transaksi::where('status_payment','Success')->where('tahun',date('Y'))
    ->where('bulan', ltrim(date('m'),'0'))->where('tgl',ltrim(date('d'),'0'))->whereIN('id_cabang_laundry', $cabang)->sum('harga_akhir');

    $incomeDOld = transaksi::where('status_payment','Success')->where('tahun',date('Y'))
    ->where('bulan', ltrim(date('m'),'0'))->where('tgl',ltrim(date("d",strtotime("-1 day")),'0'))->whereIN('id_cabang_laundry', $cabang)->sum('harga_akhir');

    $kgDay = transaksi::where('tahun', date('Y'))->where('tahun',date('Y'))->where('bulan', ltrim(date('m'),'0'))->where('tgl',ltrim(date('d'),'0'))->whereIN('id_cabang_laundry', $cabang)->sum('kg');
    $kgMonth = transaksi::where('tahun',date('Y'))->where('bulan', ltrim(date('m'),'0'))->whereIN('id_cabang_laundry', $cabang)->sum('kg');
    $kgYear = transaksi::where('tahun',date('Y'))->whereIN('id_cabang_laundry', $cabang)->sum('kg');

    $getCabang = User::whereHas('transaksi', function($a) {
      $a->where('tahun',date('Y'))
      ->where('bulan', ltrim(date('m'),'0'));
    })
    ->whereIN('id_cabang_laundry', $cabang)
    ->get();

    $target = LaundrySetting::first();

    return view('modul_admin.finance.index', \compact(
      'chartMonth','incomeY','incomeM','incomeYOld','incomeD','incomeDOld',
      'target','incomeAll','getCabang','kgDay','kgMonth','kgYear'
    ));
  }


   // Tambah dan Data Harga
    public function dataharga()
    {
      // Ambil data harga
      $harga = harga::with('harga_cabang')->orderBy('id','DESC')->get();

      // Cek Apakah sudah ada karyawan atau belum
      $karyawan = User::where('auth','Karyawan')->first();
      // Ambil list cabang
      // $getcabang = User::where('auth','Karyawan')->where('status','Active')->get();
      $getcabang = CabangLaundry::all();

      // Get Data Bank
      $getBank = DataBank::where('user_id',Auth::id())->count();

      return view('modul_admin.laundri.harga', compact('harga','karyawan','getcabang','getBank'));
    }

    // Proses Simpan Harga
    public function hargastore(HargaRequest $request)
    {
      $addharga = new harga();
      $addharga->user_id = $request->user_id;
      $addharga->jenis = $request->jenis;
      $addharga->kg = $request->jenis == 'kilogram' ? 1000 : ''; // satuan gram
      $addharga->harga = preg_replace('/[^A-Za-z0-9\-]/', '', $request->harga); // Remove special caracter
      $addharga->waktu = $request->waktu;
      $addharga->keterangan = $request->keterangan;
      $addharga->estimasi = $request->estimasi;
      $addharga->status = 1; //aktif
      $addharga->save();

      Session::flash('success','Tambah Data Harga Berhasil');
      return redirect('data-harga');
    }

    // Proses edit harga
    public function hargaedit(Request $request)
    {
      $editharga = harga::find($request->id_harga);
      $editharga->update([
          'jenis' => $request->jenis,
          'kg'    => $request->kg,
          'estimasi' => $request->estimasi,
          'keterangan' => $request->keterangan,
          'harga' => $request->harga,
          'waktu' => $request->waktu,
          'status' => $request->status,
      ]);
      Session::flash('success','Edit Data Harga Berhasil');
      return $editharga;

    }
}
