<?php

namespace App\Http\Controllers\Karyawan;

use App\Http\Controllers\Controller;
use ErrorException;
use App\Models\User;
use App\Http\Requests\AddCustomerRequest;
use Illuminate\Support\Facades\Hash;
use App\Jobs\RegisterCustomerJob;
use App\Models\CabangLaundry;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
class CustomerController extends Controller
{
    // index
    public function index()
    {
      $cabang = CabangLaundry::where('id_admin', Auth::user()->cabangLaundry->id_admin)->pluck('id')->all();
      $customer = User::whereIN('id_cabang_laundry', $cabang)
      ->where('auth','Customer')
      ->orderBy('id','DESC')->get();
      return view('karyawan.customer.index', compact('customer'));
    }

    // Detail Customer
    public function detail($id)
    {
      $customer = User::with('transaksiCustomer')
      ->where('id_cabang_laundry', Auth::user()->cabangLaundry->id)
      ->where('id',$id)->first();
      return view('karyawan.customer.detail', compact('customer'));
    }

    // Create
    public function create()
    {
      return view('karyawan.customer.create');
    }

    // Store
    public function store(AddCustomerRequest $request)
    {

      try {
        DB::beginTransaction();
        
        $phone_number = preg_replace('/^0/','62',$request->no_telp);
        $password = '12345678';

        $addCustomer = User::create([
          'karyawan_id' => Auth::user()->cabangLaundry->id_admin,
          'id_cabang_laundry' => Auth::user()->cabangLaundry->id,
          'name'        => $request->name,
          'email'       => $request->email,
          'auth'        => 'Customer',
          'level'        => 'Bronze',
          'status'      => 'Active',
          'no_telp'     => $phone_number,
          'alamat'      => $request->alamat,
          'password'    => Hash::make($password)
        ]);

        $addCustomer->assignRole($addCustomer->auth);

        if ($addCustomer) {
          // Menyiapkan data Email
          $data = array(
              'name'            => $addCustomer->name,
              'email'           => $addCustomer->email,
              'password'        => $password,
              'url_login'       => url('/login'),
              'id_cabang_laundry' => Auth::user()->cabangLaundry->nama,
          );
          // Kirim email
           if (setNotificationEmail(1) == 1) {
            dispatch(new RegisterCustomerJob($data));
           }
        }
        DB::commit();
        Session::flash('success','Customer Berhasil Ditambah !');
        return redirect('customers');
      } catch (ErrorException $e) {
        DB::rollback();
        throw new ErrorException($e->getMessage());
      }
    }
}
