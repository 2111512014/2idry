<?php

namespace App\Http\Controllers\Karyawan;

use carbon\carbon;
use ErrorException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use App\Models\{CuciKiloan, transaksi,User,harga,DataBank, ItemSatuan, Voucher, Pewangi, TipeWaktu, VoucherCustomer};
use App\Jobs\DoneCustomerJob;
use App\Jobs\OrderCustomerJob;
use App\Notifications\{OrderMasuk,OrderSelesai};
use stdClass;

class PelayananController extends Controller

{

    // Halaman list order masuk
    public function index()
    {
      $order = transaksi::where('id_cabang_laundry', Auth::user()->cabangLaundry->id)
      ->orderBy('id','DESC')->get();
      return view('karyawan.transaksi.order', compact('order'));
    }

    // Proses simpan order
    public function store(Request $request)
    {
      $request->validate([
        'status_payment' => 'required',
        'customer_id' => 'required',
      ]);
      try {
        DB::beginTransaction();
        $data_transaksi = new stdClass();
        $data_transaksi->invoice         = $request->invoice;
        $data_transaksi->tgl_transaksi   = Carbon::now()->parse($request->tgl_transaksi)->format('d-m-Y');
        $data_transaksi->status_payment  = $request->status_payment;
        $data_transaksi->customer_id     = $request->customer_id;
        $data_transaksi->user_id         = Auth::id();
        $data_transaksi->disc            = Voucher::where('id', $request->disc)->pluck('diskon')->first();
        $data_transaksi->customer        = namaCustomer($data_transaksi->customer_id);
        $data_transaksi->email_customer  = email_customer($data_transaksi->customer_id);
        $data_transaksi->jenis_pembayaran  = $request->jenis_pembayaran;
        $data_transaksi->tgl               = Carbon::now()->day;
        $data_transaksi->bulan             = Carbon::now()->month;
        $data_transaksi->tahun             = Carbon::now()->year;

        if($request->cuci_kiloan != NULL)
        {
          $request->validate([
            'cuci_kiloan' => 'required',
            'pewangi' => 'required',
            'kg' => 'required'
          ]);
          $data_transaksi->id_cuci_kiloan         = $request->cuci_kiloan;
          $data_transaksi->id_pewangi_kiloan      = $request->pewangi;
          $data_transaksi->kg_kiloan              = $request->kg;
          
          $order = new transaksi();
          $order->invoice         = $request->invoice;
          $order->tgl_transaksi   = Carbon::now()->parse($request->tgl_transaksi)->format('d-m-Y');
          $order->status_payment  = $request->status_payment;
          $order->customer_id     = $request->customer_id;
          $order->user_id         = Auth::id();
          $order->disc            = Voucher::where('id', $request->disc)->pluck('diskon')->first();
          $order->customer        = namaCustomer($order->customer_id);
          $order->email_customer  = email_customer($data_transaksi->customer_id);
          $order->jenis           = 'Kiloan';
          $order->id_cuci_kiloan  = $request->cuci_kiloan;
          $order->id_pewangi      = $request->pewangi;
          $order->kg              = $request->kg;
          $order->id_cabang_laundry = Auth::user()->cabangLaundry->id;
          $order->harga           = CuciKiloan::find($request->cuci_kiloan)->pluck('harga')->first();
          $hitung                 = $order->kg * $order->harga;
  
          if ($request->disc != NULL) {
              $disc                = ($hitung * $order->disc) / 100;
              $total               = $hitung - $disc;
              $order->harga_akhir  = $total;
          } else {
            $order->harga_akhir    = $hitung;
          }
          
          if($request->sisa_uang != NULL){
            $order->harga_akhir = $order->harga_akhir - $request->sisa_uang;
            $costumer = user::where('id', $request->customer_id)->first();
            $costumer->sisa_uang -= preg_replace('/[^A-Za-z0-9\-]/', '', $request->sisa_uang);
            $costumer->save();
          }
          $data_transaksi->jenis_pembayaran  = $request->jenis_pembayaran;
          
          $order->tgl               = Carbon::now()->day;
          $order->bulan             = Carbon::now()->month;
          $order->tahun             = Carbon::now()->year;
          $order->save();
        }

        if($request->item_satuan != NULL){
          if($request->item_satuan != NULL)
          {
            $request->validate([
              'item_satuan' => 'required',
              'pewangi_satuan' => 'required',
              'kg_satuan' => 'required'
            ]);

            $data_transaksi->id_item_satuan    = $request->item_satuan;
            $data_transaksi->id_pewangi_satuan = $request->pewangi_satuan;
            $data_transaksi->kg_satuan         = $request->kg_satuan;

            $orderSatuan = new transaksi();
            $orderSatuan->invoice         = $request->invoice;
            $orderSatuan->tgl_transaksi   = Carbon::now()->parse($orderSatuan->tgl_transaksi)->format('d-m-Y');
            $orderSatuan->status_payment  = $request->status_payment;
            $orderSatuan->customer_id     = $request->customer_id;
            $orderSatuan->user_id         = Auth::id();
            $orderSatuan->jenis           = 'Satuan';
            $orderSatuan->id_item_satuan  = $request->item_satuan;
            $orderSatuan->id_pewangi      = $request->pewangi_satuan;
            $orderSatuan->kg              = $request->kg_satuan;
            $orderSatuan->id_cabang_laundry = Auth::user()->cabangLaundry->id;
            $orderSatuan->customer        = namaCustomer($orderSatuan->customer_id);
            $orderSatuan->email_customer  = email_customer($orderSatuan->customer_id);
            $orderSatuan->harga           = ItemSatuan::find($request->item_satuan)->pluck('harga')->first();
            $orderSatuan->disc            = '';
            $hitung                 = $orderSatuan->kg * $orderSatuan->harga;
            $orderSatuan->harga_akhir       = $hitung;
            $orderSatuan->jenis_pembayaran  = $request->jenis_pembayaran;
            $orderSatuan->tgl               = Carbon::now()->day;
            $orderSatuan->bulan             = Carbon::now()->month;
            $orderSatuan->tahun             = Carbon::now()->year;
            $orderSatuan->save();
          }
      }

        if (isset($order) || isset($orderSatuan)) {
          // Notification Telegram
          if (setNotificationTelegramIn(1) == 1) {
            // $order->notify(new OrderMasuk());
          }

          // Notification email
          if (setNotificationEmail(1) == 1) {
            // Menyiapkan data Email
            $bank = DataBank::get();
            $jenisPakaian = CuciKiloan::find($request->cuci_kiloan)->pluck('nama')->first();
            $data = array(
                'email'         => $data_transaksi->email_customer,
                'invoice'       => $data_transaksi->invoice,
                'customer'      => $data_transaksi->customer,
                'tgl_transaksi' => $data_transaksi->tgl_transaksi,
                'pakaian'       => $jenisPakaian,
                'berat'         => $data_transaksi->kg,
                'harga'         => $data_transaksi->harga,
                'harga_disc'    => ($hitung * $data_transaksi->disc) / 100,
                'disc'          => $data_transaksi->disc,
                'total'         => $data_transaksi->kg * $data_transaksi->harga,
                'harga_akhir'   => $data_transaksi->harga_akhir,
                'laundry_name'  => Auth::user()->cabangLaundry->nama,
                'bank'          => $bank
            );

            // Kirim Email
            dispatch(new OrderCustomerJob($data));
          }

          // Notifikasi WhatsApp
          if (setNotificationWhatsappOrderSelesai(1) == 1 && getTokenWhatsapp() != null) {
          // Create Notifikasi
            $transaksi = transaksi::where('invoice', $request->invoice)->get();
            $id         = $transaksi[0]->id;
            $user_id    = $transaksi[0]->customer_id;
            $title      = 'Pakaian Selesai';
            $body       = 'Pakaian Sudah Selesai dan Sudah Bisa Diambil :)';
            $kategori   = 'info';
            sendNotification($id,$user_id,$kategori,$title,$body);

            // Cek status notif untuk telegram
            if (setNotificationTelegramFinish(1) == 1) {
              $transaksi[0]->notify(new OrderSelesai());
            }
            // Kirim WhatsApp
              $waCustomer = $transaksi[0]->customers->no_telp; // get nomor whatsapp customer
              $nameCustomer = $transaksi[0]->customers->name; // get name customer
                $pesan = "Nama Laundry : " . Auth::user()->cabangLaundry->nama. "\n";
                $pesan .= "Alamat : " . Auth::user()->cabangLaundry->alamat. "\n";
                $pesan .= "No. HP " . Auth::user()->no_telp. "\n";
                $pesan .= "====================". "\n";
                $pesan .= "Tanggal : " . $transaksi[0]->created_at. "\n";
                $pesan .= "No Nota : " . $transaksi[0]->invoice. "\n";
                $pesan .="Kasir : " . Auth::user()->name . "\n";
                $pesan .="Nama : " . $nameCustomer . "\n";
                $pesan .="===================". "\n\n";

                if($transaksi[0]->id_cuci_kiloan != null){
                  $pesan .= "Tipe Laundry : ";
                  $pesan .= $transaksi[0]->cuciKiloan->waktu->nama;
                  $pesan .= "\nTipe Layanan : ";
                  $pesan .= $transaksi[0]->cuciKiloan->nama;
                  
                  $pesan .="\nJenis Pewangi : ";
                  $pesan .= $transaksi[0]->pewangi->nama . "\n";
                  $pesan .="Berat (kg) : " . $transaksi[0]->kg . "\n";
                  $pesan .="Harga /kg : Rp. " . number_format($transaksi[0]->harga, '0','.',','). "\n";
                  $pesan .="Subtotal : Rp. ". number_format((int) $transaksi[0]->kg * (int) $transaksi[0]->harga) . "\n\n";
                  $total = $transaksi[0]->harga_akhir;
                }
                if(count($transaksi) > 1 || $transaksi[0]->id_item_satuan != null){
                  $key = count($transaksi) > 1 ? 1 : 0;
                  $pesan .= "Tipe Laundry : Satuan";
                  $pesan .= "\nTipe Layanan : ";
                  $pesan .= $transaksi[$key]->itemSatuan->nama;
                  
                  $pesan .="\nJenis Pewangi : ";
                  $pesan .= $transaksi[$key]->pewangi->nama . "\n";
                  $pesan .="Berat (kg) : " . $transaksi[$key]->kg . "\n";
                  $pesan .="Harga /kg : Rp. " . number_format($transaksi[$key]->harga, '0','.',','). "\n";
                  $pesan .="Subtotal : Rp. ". number_format((int) $transaksi[$key]->kg * (int) $transaksi[$key]->harga) . "\n\n";
                }
                
                $pesan .="Diskon : Rp. " . number_format($transaksi[0]->disc == "" ? 0 : $transaksi[0]->disc) . "\n";
                if(count($transaksi) > 1){
                  $total = $transaksi[0]->harga_akhir + $transaksi[1]->harga_akhir;
                }else{
                  $total = $transaksi[0]->harga_akhir;

                }
                $pesan .="Total : Rp. " . number_format($total). "\n";
                $pesan .="Status : " ;
                $pesan .= $transaksi[0]->status_payment == "Success" ? "Lunas" : "Belum Lunas". "\n";

                if($transaksi[0]->status_payment == "Success"){
                  $pesan .="Dilunasi : " . $transaksi[0]->created_at. "\n";
                }

              notificationWhatsapp(
                getTokenWhatsapp(), // Token
                $waCustomer, // nomor whatsapp
                $pesan
              );
            }

          $totalLaundryKgBulanIni = transaksi::with('cuciKiloan', 'itemSatuan')->where('customer_id', $request->customer_id)->where('tahun', date('Y'))->where('bulan', ltrim(date('m'), '0'))->sum('kg');
          $totalLaundryKgBulanLalu = transaksi::with('cuciKiloan', 'itemSatuan')->where('customer_id',Auth::id())->where('bulan', date("M", strtotime("-1 month")))->sum('kg');

          $admin = Auth::user()->cabangLaundry->admin->levelSetting;
          
          if($totalLaundryKgBulanIni >= $admin->transaksi_gold || $totalLaundryKgBulanLalu >= $admin->min_transaksi_gold){
            $level = 'Gold';
          } else if ($totalLaundryKgBulanIni >= $admin->transaksi_silver ||  $totalLaundryKgBulanLalu >= $admin->min_transaksi_silver) {
            $level = 'Silver';
          } else if ($totalLaundryKgBulanIni >= $admin->transaksi_bronze || $totalLaundryKgBulanIni <= $admin->transaksi_bronze || $totalLaundryKgBulanLalu >= $admin->min_transaksi_bronze){
            $level = 'Bronze';
          }
          
          $customer = User::where('id', $request->customer_id)->first();
          $customer->level = $level;    
          $customer->save();  
          if(isset($request->disc)){
            $voucherUpdate = VoucherCustomer::where('id_voucher', $request->disc)->where('id_customer', $request->customer_id)->first();
            $voucherUpdate->status = 'Not Active';
            $voucherUpdate->save();
          }

          DB::commit();
          Session::flash('success','Order Berhasil Ditambah !');
          return redirect('pelayanan');
        }
        Session::flash('error','Order Gagal Ditambah, Data tidak lengkap!');
        return redirect('pelayanan');
      } catch (ErrorException $e) {
        DB::rollback();
        throw new ErrorException($e->getMessage());
      }
    }

    // Tambah Order
    public function addorders()
    {
      $customer = User::where('karyawan_id', Auth::user()->cabangLaundry->id_admin)->where('auth', 'Customer')->get();
      $tipeWaktu = TipeWaktu::where('id_admin', Auth::user()->cabangLaundry->id_admin)->get();
      $pewangi = Pewangi::where('id_admin', Auth::user()->cabangLaundry->id_admin)->get();
      $itemSatuan = ItemSatuan::where('id_admin', Auth::user()->cabangLaundry->id_admin)->get();
      $y = date('Y');
      $number = mt_rand(1000, 9999);
      // Nomor Form otomatis
      $newID = $number. Auth::user()->id .''.$y;
      $tgl = date('d-m-Y');

      $cek_customer = User::select('id','karyawan_id')->where('karyawan_id', Auth::user()->cabangLaundry->id_admin)->count();
      return view('karyawan.transaksi.addorder', compact(
        'customer',
        'newID',
        'cek_customer',
        'tipeWaktu',
        'pewangi',
        'itemSatuan'
      ));
    }

    // Filter List Harga
    public function listharga(Request $request)
    {
       $list_harga = harga::select('id','harga')
        ->where('user_id',Auth::user()->cabangLaundry->id)
        ->where('id',$request->id)
        ->get();
        $select = '';
        $select .= '
                    <div class="form-group has-success">
                    <label for="id" class="control-label">Harga</label>
                    <select id="harga" class="form-control" name="harga" value="harga">
                    ';
                    foreach ($list_harga as $studi) {
        $select .= '<option value="'.$studi->harga.'">'.'Rp. ' .number_format($studi->harga,0,",",".").'</option>';
                    }'
                    </select>
                    </div>
                    </div>';
        return $select;
    }

    // Total Belanja
    public function hargaItem(Request $request)
    {
      if($request->jenis == 'Kiloan')
      {
        return CuciKiloan::where('id', $request->id)->pluck('harga')->first();
      }else{
        return ItemSatuan::where('id', $request->id)->pluck('harga')->first();
      }
    }

    // Filter List Harga
    public function listCuciKiloan(Request $request)
    {
       $cuci_kiloan = CuciKiloan::where('id_admin',Auth::user()->cabangLaundry->id_admin)
        ->where('id_waktu',$request->id)
        ->get();
        $select = '';
        $select .= '
                    <div class="form-group has-success">
                    <label for="id" class="control-label">Cuci Kiloan</label>
                    <select id="cuci_kiloan" class="form-control" name="cuci_kiloan" value="cuci_kiloan">
                    ';
        $select .= '<option value="">' . '-- Pilih Cuci Kiloan --' . ' </option>';
                    foreach ($cuci_kiloan as $item) {
        $select .= '<option value="'.$item->id.'">'.$item->nama.'</option>';
                    }'
                    </select>
                    </div>
                    </div>';
        return $select;
    }

    // Filter List Jumlah Hari
    public function listhari(Request $request)
    {
      $list_jenis = harga::select('id','estimasi','waktu')
        ->where('user_id',Auth::user()->cabangLaundry->id)
        ->where('id',$request->id)
        ->get();
        $select = '';
        $select .= '
                    <div class="form-group has-success">
                    <label for="id" class="control-label">Pilih Hari</label>
                    <select id="hari" class="form-control" name="hari" value="hari">
                    ';
                    foreach ($list_jenis as $hari) {
        $select .= '<option value="'.$hari->estimasi.'">'. $hari->estimasi . ' '. $hari->waktu .'</option>';
                    }'
                    </select>
                    </div>
                    </div>';
        return $select;
    }

    // Filter jenis pakaian
    public function listpakaian(Request $request)
    {
      $list_pakaian = harga::select('id','keterangan')
        ->where('user_id',Auth::user()->cabangLaundry->id)
        ->where('jenis',$request->jenis_layanan)
        ->get();
        $select = '';
        $select .= '
                    <div class="form-group has-success">
                    <label for="id" class="control-label">Pilih Pakaian</label>
                    <select id="id" class="form-control" name="harga_id" value="keterangan">
                    ';
                    $select .= '<option value="">' . 'Pilih' . ' </option>';
                    foreach ($list_pakaian as $pakaian) {
        $select .= '<option value="'.$pakaian->id.'">'. $pakaian->keterangan  .'</option>';
                    }'
                    </select>
                    </div>
                    </div>';
        return $select;
    }

    // Filter jenis pakaian
    public function listVoucher(Request $request)
    {
      $voucher = VoucherCustomer::with('voucher')->where('id_customer', $request->id_customer)
        ->where('status','Active')
        ->get();
        $select = '';
        $select .= '
                    <div class="form-group has-success">
                    <label for="id" class="control-label">Voucher</label>
                    <select id="disc" class="form-control" name="disc" value="keterangan">
                    ';
                    $select .= '<option value="">' . '-- Voucher --' . ' </option>';
                    foreach ($voucher as $item) {
        $select .= '<option value="'.$item->voucher->id.'">'. $item->voucher->nama  .'</option>';
                    }'
                    </select>
                    </div>
                    </div>';
        return $select;
    }


    // Update Status Laundry
    public function updateStatusLaundry(Request $request)
    {
      $transaksi = transaksi::find($request->id);
    if ($transaksi->status_order == 'Unprocessed') {
      $transaksi->update([
        'status_order' => 'Process'
      ]);
        $title      = 'Prosess';
        $body       = 'Cucian Sedang di Cuci :)';
        $body       .= "\n\nTerima Kasih :)";
      } elseif ($transaksi->status_order == 'Process') {
        $transaksi->update([
          'status_order' => 'Delivery'
        ]);

          // Tambah point +1
          $points = User::where('id',$transaksi->customer_id)->firstOrFail();
          $points->point =  $points->point + 1;
          $points->update();

          $title      = 'Selesai';
          $body       = 'Cucian Sudah Selesai dan Sudah Bisa Diambil :)';
          $body       .= "\n\nTerima Kasih :)";

      } elseif ($transaksi->status_order == 'Delivery') {
        $transaksi->update([
          'status_order' => 'Done'
        ]);
          $title      = 'Selesai';
          $body       = "Cucian Selesai :)";
          $body       .= "\n\nTerima Kasih :)";
      }

      if($transaksi->status_order == 'Done' || $transaksi->status_order == 'Delivery') {
          Session::flash('success', "Status Laundry Berhasil Diubah !");
      }
      
      // Create Notifikasi
          $id         = $transaksi->id;
          $user_id    = $transaksi->customer_id;
          
          $kategori   = 'info';
          sendNotification($id,$user_id,$kategori,$title,$body);

          // Cek email notif
          if (setNotificationEmail(1) == 1) {

            // Menyiapkan data
            $data = array(
                'email'           => $transaksi->email_customer,
                'invoice'         => $transaksi->invoice,
                'customer'        => $transaksi->customer,
                'nama_laundry'    => Auth::user()->cabangLaundry->nama,
                'alamat_laundry'  => Auth::user()->cabangLaundry->alamat,
            );

          // Kirim Email
          dispatch(new DoneCustomerJob($data));
          }

          // Cek status notif untuk telegram
          if (setNotificationTelegramFinish(1) == 1) {
            $transaksi->notify(new OrderSelesai());
          }

          // Notifikasi WhatsApp
          if (setNotificationWhatsappOrderSelesai(1) == 1 && getTokenWhatsapp() != null) {
            $waCustomer = $transaksi->customers->no_telp; // get nomor whatsapp customer
            $nameCustomer = $transaksi->customers->name; // get name customer
            $pesan = "Nama Laundry : " . Auth::user()->cabangLaundry->nama. "\n";
            $pesan .= "Alamat : " . Auth::user()->cabangLaundry->alamat. "\n";
            $pesan .= "No. HP " . Auth::user()->no_telp. "\n";
            $pesan .= "====================". "\n";
            $pesan .= "Tanggal : " . $transaksi->created_at. "\n";
            $pesan .= "No Nota : " . $transaksi->invoice. "\n";
            $pesan .="Kasir : " . Auth::user()->name . "\n";
            $pesan .= "Nama : " . $nameCustomer . "\n";
            $pesan .= "===================". "\n\n";
            $pesan .= $title . "\n";
            $pesan .= $body . "\n";

            notificationWhatsapp(
              getTokenWhatsapp(), // Token
              $waCustomer, // nomor whatsapp
              $pesan // pesan
            );
          }
    }

    // Update Status Laundry
    public function updatePaymentLaundry(Request $request)
    {
      $transaksi = transaksi::find($request->id);
      if ($transaksi->status_payment == 'Pending') {
        $transaksi->update([
          'status_payment' => 'Success'
        ]);
      }

      if ($transaksi->status_payment == 'Success') {
        Session::flash('success', "Status Pembayaran Berhasil Diubah !");
      }
    }

    // Update Status Laundry
    public function reminderLaundry(Request $request)
    {
      $transaksi = transaksi::find($request->id);
      if (setNotificationWhatsappOrderSelesai(1) == 1 && getTokenWhatsapp() != null) {
        $waCustomer = $transaksi->customers->no_telp; // get nomor whatsapp customer
        $nameCustomer = $transaksi->customers->name; // get name customer
        $pesan = "Nama Laundry : " . Auth::user()->cabangLaundry->nama. "\n";
        $pesan .= "Alamat : " . Auth::user()->cabangLaundry->alamat. "\n";
        $pesan .= "No. HP " . Auth::user()->no_telp. "\n";
        $pesan .= "====================". "\n";
        $pesan .= "Tanggal : " . $transaksi->created_at. "\n";
        $pesan .= "No Nota : " . $transaksi->invoice. "\n";
        $pesan .="Kasir : " . Auth::user()->name . "\n";
        $pesan .= "Nama : " . $nameCustomer . "\n";
        $pesan .= "===================". "\n\n";
        $pesan .= "*Reminder*\n";
        $pesan .= "Cucian Kamu Sudah Selesai, Silahkan di jemput ya :) \n\n Terima Kasih :)";

        notificationWhatsapp(
          getTokenWhatsapp(), // Token
          $waCustomer, // nomor whatsapp
          $pesan // pesan
        );
        Session::flash('success', "Reminder Terkirim !");
      }
    }

    // Tambah Edit Catatan
    public function tambahCatatan(Request $request)
    {
      $transaksi = transaksi::where('invoice',$request->invoice)->first();
      $transaksi->catatan = $request->catatan;
      $transaksi->save();

      Session::flash('success', "Catatan berhasil ditambahkan !");
    }

    public function updateSisaUang(Request $request)
    {
      try {
        DB::beginTransaction();
        $costumer = user::where('id', $request->id_costumer)->first();
        $costumer->sisa_uang += preg_replace('/[^A-Za-z0-9\-]/', '', $request->sisa_uang);
        $costumer->save();

        DB::commit();
        Session::flash('success', 'Sisa Uang berhasil ditambahkan !');
        return ;
        } catch (ErrorException $e) {
          DB::rollback();
          throw new ErrorException($e->getMessage());
        }
      }

    public function sisaUangShow(Request $request){
      $sisaUang = User::where('id',$request->customer_id)->pluck('sisa_uang')->first();
      return number_format($sisaUang, 0, ",", ".");
    }
}
