<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Exports\AdminExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\AddAdminRequest;
use ErrorException;
use App\Models\User;
use Illuminate\Http\Request;
use App\Jobs\RegisterAdminJob;
use Illuminate\Support\Facades\Hash;
use App\Models\LaundrySetting;
use App\Models\LevelSetting;
use App\Models\notifications_setting;
use App\Models\PageSettings;
use Carbon\Carbon;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;

class AdminController extends Controller
{
    // index
    public function index()
    {
      $admin = User::where('auth','Admin')
      ->orderBy('id','DESC')->get();
      return view('super_admin.admin.index', compact('admin'));
    }

    // Detail Customer
    public function detail($id)
    {
      $admin = User::where('id',$id)->first();
      return view('super_admin.admin.detail', compact('admin'));
    }

    // Create
    public function create()
    {
      return view('super_admin.admin.create');
    }

    // Create
    public function edit($id)
    {
      $admins = User::find($id);
      return view('super_admin.admin.edit', compact('admins'));
    }

    // Store
    public function store(AddAdminRequest $request)
    {

      try {
        DB::beginTransaction();

        $phone_number = preg_replace('/^0/','62',$request->no_telp);
        $password = '12345678';

        $addAdmin = User::create([
          'karyawan_id' => Auth::id(),
          'name'        => $request->name,
          'email'       => $request->email,
          'auth'        => 'Admin',
          'status'      => 'Active',
          'no_telp'     => $phone_number,
          'password'    => Hash::make($request->password)
        ]);
        $admin = User::where('name',$request->name)->where('email', $request->email)->first();
        
        PageSettings::create([
          'id_admin' => $admin->id,
          'judul'   => 'E-Laundry'
        ]);
            
        $setting = new LaundrySetting();
        $setting->user_id       = $admin->id;
        $setting->target_day    = 0;
        $setting->target_month  = 0;
        $setting->target_year   = 0;
        $setting->save();

        $level = new LevelSetting();
        $level->user_id             = $admin->id;
        $level->transaksi_gold      = 0;
        $level->transaksi_silver    = 0;
        $level->transaksi_bronze    = 0;
        $level->min_transaksi_gold      = 0;
        $level->min_transaksi_silver    = 0;
        $level->min_transaksi_bronze    = 0;
        $level->save();
        
        $notif = new notifications_setting();
        $notif->user_id = $setting->user_id;
        $notif->telegram_order_masuk    = 0;
        $notif->telegram_order_selesai  = 0;
        $notif->email                   = 0;
        $notif->save();
        
        $addAdmin->assignRole($addAdmin->auth);

        if ($addAdmin) {
          // Menyiapkan data Email
          $data = array(
              'name'            => $addAdmin->name,
              'email'           => $addAdmin->email,
              'password'        => $password,
              'url_login'       => url('/login')
          );
          // Kirim email
           if (setNotificationEmail(1) == 1) {
            dispatch(new RegisterAdminJob($data));
           }
        }
        DB::commit();
        Session::flash('success','Customer Berhasil Ditambah !');
        return redirect('list-admin');
      } catch (ErrorException $e) {
        DB::rollback();
        throw new ErrorException($e->getMessage());
      }
    }

    // Update
    public function update(Request $request,$id)
    {

      try {
        DB::beginTransaction();

        $phone_number = preg_replace('/^0/','62',$request->no_telp);

        $admin = User::find($id);
        
        $admin->name = $request->name;
        $admin->email = $request->email;
        $admin->no_telp = $phone_number;
        if($request->password){
          $admin->password = Hash::make($request->password);
        }
        $admin->save();

        DB::commit();
        Session::flash('success','Customer Berhasil Ditambah !');
        return redirect('list-admin');
      } catch (ErrorException $e) {
        DB::rollback();
        throw new ErrorException($e->getMessage());
      }
    }

    // Update Status Karyawan
    public function updateStatusAdmin(Request $request)
    {
      $admin = User::find($request->id);
      $admin->update([
        'status'  => $admin->status == 'Active' ? 'Not Active' : 'Active'
      ]);

      Session::flash('success','Status Admin Berhasil Diupdate.');
    }

    // Export Admin
    public function exportAdmin()
    {
      return Excel::download(new AdminExport, 'Daftar Admin ' . Carbon::now()->format('F').'.xlsx');
    }
}
