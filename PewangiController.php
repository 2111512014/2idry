<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use ErrorException;
use App\Http\Requests\AddPewangiRequest;
use App\Models\Pewangi;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class PewangiController extends Controller
{
    // index
    public function index()
    {
      $pewangi = Pewangi::where('id_admin', Auth::id())->get();
      return view('modul_admin.pewangi.index', compact('pewangi'));
    }

    // Create
    public function create()
    {
      return view('modul_admin.pewangi.create');
    }

    // Store
    public function store(AddPewangiRequest $request)
    {
      try {
        DB::beginTransaction();
        Pewangi::create([
          'id_admin'    => Auth::id(),
          'nama'        => $request->nama,
          'harga'       => $request->harga,
          'status'      => $request->status,
        ]);

        DB::commit();
        Session::flash('success','Pewangi Berhasil Ditambah !');
        return redirect('pewangi');
      } catch (ErrorException $e) {
        DB::rollback();
        throw new ErrorException($e->getMessage());
      }
    }

    // Create
    public function edit($id)
    {
      $item = Pewangi::find($id);
      return view('modul_admin.pewangi.edit', compact('item'));
    }

    // Update 
    public function update(Request $request)
    {
      $pewangi = Pewangi::find($request->id);
      $pewangi->update([
          'nama'        => $request->nama,
          'harga'       => $request->harga,
          'status'      => $request->status
      ]);

      Session::flash('success','Pewangi Berhasil Diupdate.');
      return redirect('pewangi');
    }

    public function destroy($id)
    {
        Pewangi::destroy($id);
        return redirect('pewangi');
    }
}
