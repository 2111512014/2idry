<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\AddCuciKiloanRequest;
use ErrorException;
use App\Models\CuciKiloan;
use App\Models\TipeWaktu;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class CuciKiloanController extends Controller
{
    // index
    public function index()
    {
      $cuciKiloan = CuciKiloan::where('id_admin', Auth::id())->get();
      return view('modul_admin.cuci_kiloan.index', compact('cuciKiloan'));
    }
    
    // Create
    public function create()
    {
      $tipeWaktu = TipeWaktu::where('id_admin', Auth::id())->get();
      return view('modul_admin.cuci_kiloan.create', compact('tipeWaktu'));
    }

    // Store
    public function store(AddCuciKiloanRequest $request)
    {
      try {
        DB::beginTransaction();
        CuciKiloan::create([
          'id_admin'    => Auth::id(),
          'nama'        => $request->nama,
          'harga'       => $request->harga,
          'jenis'       => $request->jenis,
          'id_waktu'    => $request->id_waktu,
          'status'      => $request->status,
        ]);

        DB::commit();
        Session::flash('success','Cuci Kiloan Berhasil Ditambah !');
        return redirect('cuci-kiloan');
      } catch (ErrorException $e) {
        DB::rollback();
        throw new ErrorException($e->getMessage());
      }
    }

    // Create
    public function edit($id)
    {
      $tipeWaktu = TipeWaktu::where('id_admin', Auth::id())->get();
      $item = CuciKiloan::find($id);
      return view('modul_admin.cuci_kiloan.edit', compact('item','tipeWaktu'));
    }

    // Update 
    public function update(Request $request)
    {
      $pewangi = CuciKiloan::find($request->id);
      $pewangi->update([
          'nama'        => $request->nama,
          'harga'       => $request->harga,
          'jenis'       => $request->jenis,
          'id_waktu'    => $request->id_waktu,
          'status'      => $request->status,
      ]);

      Session::flash('success','Cuci Kiloan Berhasil Diupdate.');
      return redirect('cuci-kiloan');
    }

    public function destroy($id)
    {
        CuciKiloan::destroy($id);
        return redirect('cuci-kiloan');
    }
}
