<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use ErrorException;
use App\Http\Requests\AddTipeWaktuRequest;
use App\Models\TipeWaktu;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class TipeWaktuController extends Controller
{
    // index
    public function index()
    {
      $tipeWaktu  = TipeWaktu::where('id_admin', Auth::id())->get();
      return view('modul_admin.tipe_waktu.index', compact('tipeWaktu'));
    }

    // Create
    public function create()
    {
      return view('modul_admin.tipe_waktu.create');
    }

    // Store
    public function store(AddTipeWaktuRequest $request)
    {
      try {
        DB::beginTransaction();
        TipeWaktu::create([
          'id_admin'    => Auth::id(),
          'nama'        => $request->nama,
          'waktu'       => $request->waktu,
          'tipe_waktu'  => $request->tipe_waktu,
          'jenis'       => $request->jenis,
          'status'      => $request->status
        ]);

        DB::commit();
        Session::flash('success','Tipe Waktu Berhasil Ditambah !');
        return redirect('tipe-waktu');
      } catch (ErrorException $e) {
        DB::rollback();
        throw new ErrorException($e->getMessage());
      }
    }

    // Create
    public function edit($id)
    {
      $item = TipeWaktu::find($id);
      return view('modul_admin.tipe_waktu.edit', compact('item'));
    }

    // Update 
    public function update(Request $request)
    {
      $tipe_waktu = TipeWaktu::find($request->id);
      $tipe_waktu->update([
          'nama'        => $request->nama,
          'waktu'       => $request->waktu,
          'tipe_waktu'  => $request->tipe_waktu,
          'jenis'       => $request->jenis,
          'status'      => $request->status
      ]);

      Session::flash('success','Tipe Waktu Berhasil Diupdate.');
      return redirect('tipe-waktu');
    }

    public function destroy($id)
    {
        TipeWaktu::destroy($id);
        return redirect('tipe-waktu');
    }
}
