<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CabangLaundry;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Session;

class CabangLaundryController extends Controller
{

    public function index()
    {
      $cabang_laundrys = CabangLaundry::where('id_admin', Auth::id())->get();
      return view('modul_admin.cabang-laundry.index', compact('cabang_laundrys'));
    }
  
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
      return view('modul_admin.cabang-laundry.add');
    }

    
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $addCabangLaundry = New CabangLaundry();
        $addCabangLaundry->id_admin = Auth::id();
        $addCabangLaundry->nama = $request->name;
        $addCabangLaundry->alamat = $request->alamat_cabang;
        $addCabangLaundry->save();

      Session::flash('success','Cabang Laundry Berhasil Dibuat.');
      return redirect('cabang-laundry');
    }
 /**
     * Show the form for editing a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
      $cabang_laundry = CabangLaundry::find($id);
      return view('modul_admin.cabang-laundry.edit', compact('cabang_laundry'));
    }

    // Update Status Karyawan
    public function update(Request $request,$id)
    {
      $cabang_laundry = CabangLaundry::find($id);
      $cabang_laundry->update([
        'nama' => $request->name,
        'alamat' => $request->alamat_cabang
      ]);

      Session::flash('success', 'Status Cabang Laundry Berhasil Diupdate.');
      return redirect('cabang-laundry');
    }
    // public function show($id)
    // {
    //   $customer = User::with('transaksiCustomer')->where('id',$id)->first();
    //   return view('modul_admin.customer.infoCustomer', compact('customer'));
    // }
}
