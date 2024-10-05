<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use ErrorException;
use App\Models\Katalog;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class KatalogController extends Controller
{
    // index
    public function index()
    {
      $katalog = Katalog::where('id_admin', Auth::id())->get();
      return view('modul_admin.katalog.index', compact('katalog'));
    }

    // Create
    public function create()
    {
      return view('modul_admin.katalog.create');
    }

    // Store
    public function store(Request $request)
    {
      try {
        DB::beginTransaction();
        // menyimpan data file yang diupload ke variabel $file
        $gambar = $request->file('gambar');
        $nama_gambar = time()."_".$gambar->getClientOriginalName();
    
        // isi dengan nama folder tempat kemana gambar diupload
        $tujuan_upload = 'data_katalog';
        $gambar->move($tujuan_upload,$nama_gambar);

        Katalog::create([
          'id_admin'    => Auth::id(),
          'nama'        => $request->nama,
          'harga'       => $request->harga,
          'gambar'      => $nama_gambar,
          'status'      => $request->status,
        ]);

        DB::commit();
        Session::flash('success','Katalog Berhasil Ditambah !');
        return redirect('katalog');
      } catch (ErrorException $e) {
        DB::rollback();
        throw new ErrorException($e->getMessage());
      }
    }

    // Create
    public function edit($id)
    {
      $item = Katalog::find($id);
      return view('modul_admin.katalog.edit', compact('item'));
    }

    // Update 
    public function update(Request $request)
    {
      $katalog = Katalog::find($request->id);
      if(isset($request->gambar)){
        $gambar = $request->file('gambar');
    
        $nama_gambar = time()."_".$gambar->getClientOriginalName();
    
        // isi dengan nama folder tempat kemana gambar diupload
        $tujuan_upload = 'data_katalog';
        $gambar->move($tujuan_upload,$nama_gambar);

        $katalog->update([
            'nama'        => $request->nama,
            'harga'       => $request->harga,
            'gambar'      => $nama_gambar,
            'status'      => $request->status
        ]);
      }else{
        $katalog->update([
            'nama'        => $request->nama,
            'harga'       => $request->harga,
            'status'      => $request->status
        ]);
      }

      Session::flash('success','Katalog Berhasil Diupdate.');
      return redirect('katalog');
    }

    public function destroy($id)
    {
        Katalog::destroy($id);
        return redirect('katalog');
    }
}
