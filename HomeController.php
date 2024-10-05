<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\{CabangLaundry, Katalog, Voucher, Notification, transaksi,User, VoucherCustomer};

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');

    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        if(Auth::check()){
            if (Auth::user()->auth === "Super Admin") {
                $admin = User::where('auth', 'Admin')->get();
                $masuk = transaksi::whereIN('status_order', ['Process', 'Done', 'Delivery'])->count();
                $selesai = transaksi::where('status_order', 'Done')->count();
                $diambil = transaksi::where('status_order', 'Delivery')->count();
                $customer = User::where('auth', 'Customer')->get();
                $sudahbayar = transaksi::where('status_payment', 'Success')->count();
                $belumbayar = transaksi::where('status_payment', 'Pending')->count();
                $incomeY = transaksi::where('status_payment', 'Success')
                ->where('tahun', date('Y'))->sum('harga_akhir');

                $incomeM = transaksi::where('status_payment', 'Success')
                ->where('tahun', date('Y'))->where('bulan', ltrim(date('m'), '0'))->sum('harga_akhir');

                $incomeYOld = transaksi::where('status_payment', 'Success')
                ->where('tahun', date("Y", strtotime("-1 month")))->sum('harga_akhir');

                $incomeD = transaksi::where('status_payment', 'Success')
                ->where('tahun', date('Y'))->where('bulan', ltrim(date('m'), '0'))->where('tgl', ltrim(date('d'), '0'))->sum('harga_akhir');

                $incomeDOld = transaksi::where('status_payment', 'Success')->where('tahun', date('Y'))
                ->where('bulan', ltrim(date('m'), '0'))->where('tgl', ltrim(date("d", strtotime("-1 day")), '0'))->sum('harga_akhir');

                $data = DB::table("transaksis")
                ->select("id", DB::raw("(COUNT(*)) as customer"))
                ->orderBy('created_at')
                ->groupBy(DB::raw("MONTH(created_at)"))
                ->count();

                // Statistik Harian
                $hari = DB::table('transaksis')
                ->select('tgl', DB::raw('count(id) AS jml'))
                ->whereYear('created_at', '=', date("Y", strtotime(now())))
                    ->whereMonth('created_at', '=', date("m", strtotime(now())))
                    ->groupBy('tgl')
                    ->get();

                $tanggal = '';
                $batas =  31;
                $nilai = '';
                for ($_i = 1; $_i <= $batas; $_i++) {
                    $tanggal = $tanggal . (string)$_i . ',';
                    $_check = false;
                    foreach ($hari as $_data) {
                        if ((int)@$_data->tgl === $_i) {
                            $nilai = $nilai . (string)$_data->jml . ',';
                            $_check = true;
                        }
                    }
                    if (!$_check) {
                        $nilai = $nilai . '0,';
                    }
                }

                // Statistik Bulanan
                $bln = DB::table('transaksis')
                ->select('bulan', DB::raw('count(id) AS jml'))
                ->whereYear('created_at', '=', date("Y", strtotime(now())))
                    ->whereMonth('created_at', '=', date("m", strtotime(now())))
                    ->groupBy('bulan')
                    ->get();

                $bulans = '';
                $batas =  12;
                $nilaiB = '';
                for ($_i = 1; $_i <= $batas; $_i++) {
                    $bulans = $bulans . (string)$_i . ',';
                    $_check = false;
                    foreach ($bln as $_data) {
                        if ((int)@$_data->bulan === $_i) {
                            $nilaiB = $nilaiB . (string)$_data->jml . ',';
                            $_check = true;
                        }
                    }
                    if (!$_check) {
                        $nilaiB = $nilaiB . '0,';
                    }
                }

                return view('super_admin.index')
                ->with('data', $data)
                ->with('masuk', $masuk)
                ->with('selesai', $selesai)
                ->with('customer', $customer)
                    ->with('sudahbayar', $sudahbayar)
                    ->with('belumbayar', $belumbayar)
                    ->with('_tanggal', substr($tanggal, 0, -1))
                    ->with('_nilai', substr($nilai, 0, -1))
                    ->with('_bulan', substr($bulans, 0, -1))
                    ->with('_nilaiB', substr($nilaiB, 0, -1))
                    ->with('diambil', $diambil)
                    ->with('incomeY', $incomeY)
                    ->with('incomeM', $incomeM)
                    ->with('incomeYOld', $incomeYOld)
                    ->with('incomeD', $incomeD)
                    ->with('incomeDOld', $incomeDOld)
                    ->with('admin', $admin);
            }elseif (Auth::user()->auth === "Admin") {
              $cabang = CabangLaundry::where('id_admin', Auth::id())->pluck('id')->all();
              $masuk = transaksi::whereIN('status_order',['Unprocessed','Process','Done','Delivery'])->whereIN('id_cabang_laundry',$cabang)->count();
              $selesai = transaksi::where('status_order','Done')->whereIN('id_cabang_laundry', $cabang)->count();
              $piutang = transaksi::where('status_payment','Pending')->whereIN('id_cabang_laundry', $cabang)->sum('harga_akhir');
              $customer = User::where('auth','Customer')->whereIN('id_cabang_laundry', $cabang)->get();
              $sudahbayar = transaksi::where('status_payment','Success')->whereIN('id_cabang_laundry', $cabang)->count();
              $belumbayar = transaksi::where('status_payment','Pending')->whereIN('id_cabang_laundry', $cabang)->count();
              $incomeY = transaksi::where('status_payment','Success')
              ->where('tahun',date('Y'))->whereIN('id_cabang_laundry', $cabang)->sum('harga_akhir');

              $incomeM = transaksi::where('status_payment','Success')
              ->where('tahun',date('Y'))->where('bulan', ltrim(date('m'),'0'))->whereIN('id_cabang_laundry', $cabang)->sum('harga_akhir');

              $incomeYOld = transaksi::where('status_payment','Success')
              ->where('tahun',date("Y",strtotime("-1 month")))->whereIN('id_cabang_laundry', $cabang)->sum('harga_akhir');

              $incomeD = transaksi::where('status_payment','Success')
              ->where('tahun',date('Y'))->where('bulan', ltrim(date('m'),'0'))->where('tgl',ltrim(date('d'),'0'))->whereIN('id_cabang_laundry', $cabang)->sum('harga_akhir');

              $incomeDOld = transaksi::where('status_payment','Success')->where('tahun',date('Y'))
              ->where('bulan', ltrim(date('m'),'0'))->where('tgl',ltrim(date("d",strtotime("-1 day")),'0'))->whereIN('id_cabang_laundry', $cabang)->sum('harga_akhir');

              $data = DB::table("transaksis")
                  ->select("id" ,DB::raw("(COUNT(*)) as customer"))
                  ->orderBy('created_at')
                  ->whereIN('id_cabang_laundry', $cabang)
                  ->groupBy(DB::raw("MONTH(created_at)"))
                  ->count();

              // Statistik Harian
              $hari = DB::table('transaksis')
              ->  select('tgl', DB::raw('count(id) AS jml'))
              ->  whereYear('created_at','=',date("Y", strtotime(now())))
              ->  whereMonth('created_at','=',date("m", strtotime(now())))
              ->  whereIN('id_cabang_laundry', $cabang)
              ->  groupBy('tgl')
              ->  get();

              $tanggal = '';
              $batas =  31;
              $nilai = '';
              for($_i=1; $_i <= $batas; $_i++){
                  $tanggal = $tanggal . (string)$_i . ',';
                  $_check = false;
                  foreach($hari as $_data){
                      if((int)@$_data->tgl === $_i){
                          $nilai = $nilai . (string)$_data->jml . ',';
                          $_check = true;
                      }
                  }
                  if(!$_check){
                      $nilai = $nilai . '0,';
                  }
              }

              // Statistik Bulanan
              $bln = DB::table('transaksis')
              ->  select('bulan', DB::raw('count(id) AS jml'))
              ->  whereYear('created_at','=',date("Y", strtotime(now())))
              ->  whereMonth('created_at','=',date("m", strtotime(now())))
              ->  whereIN('id_cabang_laundry', $cabang)
              ->  groupBy('bulan')
              ->  get();

              $bulans = '';
              $batas =  12;
              $nilaiB = '';
              for($_i=1; $_i <= $batas; $_i++){
                  $bulans = $bulans . (string)$_i . ',';
                  $_check = false;
                  foreach($bln as $_data){
                      if((int)@$_data->bulan === $_i){
                          $nilaiB = $nilaiB . (string)$_data->jml . ',';
                          $_check = true;
                      }
                  }
                  if(!$_check){
                      $nilaiB = $nilaiB . '0,';
                  }
              }

              return view('modul_admin.index')
                  ->  with('data', $data)
                  ->  with('masuk',$masuk)
                  ->  with('selesai',$selesai)
                  ->  with('customer', $customer)
                  ->  with('sudahbayar', $sudahbayar)
                  ->  with('belumbayar', $belumbayar)
                  ->  with('_tanggal', substr($tanggal, 0,-1))
                  ->  with('_nilai', substr($nilai, 0, -1))
                  ->  with('_bulan', substr($bulans, 0,-1))
                  ->  with('_nilaiB', substr($nilaiB, 0, -1))
                  ->  with('piutang',$piutang)
                  ->  with('incomeY',$incomeY)
                  ->  with('incomeM',$incomeM)
                  ->  with('incomeYOld',$incomeYOld)
                  ->  with('incomeD',$incomeD)
                  ->  with('incomeDOld',$incomeDOld);

          } elseif(Auth::user()->auth === "Karyawan") {
              $cabang = CabangLaundry::where('id_admin', Auth::user()->cabangLaundry->id_admin)->pluck('id')->all();
              $masuk = transaksi::whereIN('status_order',['Process','Done','Delivery'])->where('id_cabang_laundry', Auth::user()->cabangLaundry->id)->count();
              $selesai = transaksi::where('status_order','Done')->where('id_cabang_laundry', Auth::user()->cabangLaundry->id)->count();
              $diambil = transaksi::where('status_order','Delivery')->where('id_cabang_laundry', Auth::user()->cabangLaundry->id)->count();
              $customer = User::where('auth', 'Customer')->where('id_cabang_laundry', Auth::user()->cabangLaundry->id)->get();

              $kgToday = transaksi::where('id_cabang_laundry', Auth::user()->cabangLaundry->id)->where('tahun',date('Y'))
              ->where('bulan', ltrim(date('m'),'0'))->where('tgl',ltrim(date('d'),'0'))->sum('kg');

              $kgTodayOld = transaksi::where('id_cabang_laundry', Auth::user()->cabangLaundry->id)->where('tahun',date('Y'))
              ->where('bulan', ltrim(date('m'),'0'))->where('tgl',ltrim(date("d",strtotime("-1 day")),'0'))->sum('kg');

              $incomeM = transaksi::where('id_cabang_laundry', Auth::user()->cabangLaundry->id)->where('status_payment','Success')
              ->where('tahun',date('Y'))->where('bulan', ltrim(date('m'),'0'))->sum('harga_akhir');

              $incomeMOld = transaksi::where('id_cabang_laundry', Auth::user()->cabangLaundry->id)->where('status_payment','Success')
              ->where('tahun',date('Y'))->where('bulan', ltrim(date('m',strtotime("-1 month")),'0'))->sum('harga_akhir');

              $persen = 0;
              if ($incomeMOld != null && $incomeM != null) {
                $persen =  ($incomeM - $incomeMOld) / $incomeM * 100;
              }

              // Statistik Bulanan
              $bln = DB::table('transaksis')
              ->  select('bulan', DB::raw('count(id) AS jml'))
              ->  whereYear('created_at','=',date("Y", strtotime(now())))
              ->  whereMonth('created_at','=',date("m", strtotime(now())))
              ->  groupBy('bulan')
              ->  get();

              $bulans = '';
              $batas =  12;
              $nilaiB = '';
              for($_i=1; $_i <= $batas; $_i++){
                  $bulans = $bulans . (string)$_i . ',';
                  $_check = false;
                  foreach($bln as $_data){
                      if((int)@$_data->bulan === $_i){
                          $nilaiB = $nilaiB . (string)$_data->jml . ',';
                          $_check = true;
                      }
                  }
                  if(!$_check){
                      $nilaiB = $nilaiB . '0,';
                  }
              }

              return view('karyawan.index')
                  ->  with('diambil', $diambil)
                  ->  with('masuk',$masuk)
                  ->  with('selesai',$selesai)
                  ->  with('customer', $customer)
                  ->  with('kgToday', $kgToday)
                  ->  with('kgTodayOld', $kgTodayOld)
                  ->  with('incomeM',$incomeM)
                  ->  with('incomeMOld',$incomeMOld)
                  ->  with('persen',$persen)
                  ->  with('_bulan', substr($bulans, 0,-1))
                  ->  with('_nilaiB', substr($nilaiB, 0, -1));

          }elseif(Auth::user()->auth == 'Customer'){    
            $katalog = Katalog::where('id_admin', Auth::user()->cabangLaundry->admin->id)->where('status', 'Active')->get();   
            $voucherCustomer = VoucherCustomer::where('id_customer', Auth::id())->pluck('id_voucher')->all();
            $voucher = Voucher::where('level', Auth::user()->level)->where('status', 'Active')->whereNotIn('id', $voucherCustomer)->get();
            $totalLaundry = transaksi::with('cuciKiloan', 'itemSatuan')->where('customer_id',Auth::id())->count();
            $totalLaundryKg = transaksi::with('cuciKiloan', 'itemSatuan')->where('customer_id',Auth::id())->sum('kg');
            $totalLaundryKgBulanIni = transaksi::with('cuciKiloan', 'itemSatuan')->where('customer_id',Auth::id())->where('tahun', date('Y'))
                    ->where('bulan', ltrim(date('m'), '0'))->sum('kg');
            $totalLaundryKgBulanLalu = transaksi::with('cuciKiloan', 'itemSatuan')->where('customer_id',Auth::id())->where('bulan', date("M", strtotime("-1 month")))->sum('kg');
            
            
            $admin = Auth::user()->cabangLaundry->admin->levelSetting;
            
            if($totalLaundryKgBulanIni >= $admin->transaksi_gold || $totalLaundryKgBulanLalu >= $admin->min_transaksi_gold){
                $level = 'Gold';
            } else if ($totalLaundryKgBulanIni >= $admin->transaksi_silver ||  $totalLaundryKgBulanLalu >= $admin->min_transaksi_silver) {
                $level = 'Silver';
            } else if ($totalLaundryKgBulanIni >= $admin->transaksi_bronze || $totalLaundryKgBulanIni <= $admin->transaksi_bronze|| $totalLaundryKgBulanLalu >= $admin->min_transaksi_bronze){
                $level = 'Bronze';
            }
            
            $customer = User::where('id', Auth::id())->first();
            $customer->level = $level;      
            $customer->save();

            $transaksi = transaksi::with('cuciKiloan', 'itemSatuan')->where('customer_id',Auth::id())->orderBy('tgl_transaksi','desc')->get();
            return view('customer.index',\compact('totalLaundry','totalLaundryKg','transaksi', 'totalLaundryKgBulanIni', 'totalLaundryKgBulanLalu', 'voucher', 'katalog'));
          }
        }
    }

    // Read Notifikasi
    public function readNotifikasi(Request $request)
    {
        $notif = Notification::find($request->id);
        $notif->update([
            'is_read'   => 1
        ]);

        return $notif;
    }

}
