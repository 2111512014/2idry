<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use ErrorException;
use App\Http\Requests\AddItemSatuanRequest;
use App\Models\ItemSatuan;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class ItemSatuanController extends Controller
{
    // index
    public function index()
    {
      $itemSatuan = ItemSatuan::where('id_admin', Auth::id())->get();
      return view('modul_admin.item_satuan.index', compact('itemSatuan'));
    }

    // Create
    public function create()
    {
      return view('modul_admin.item_satuan.create');
    }

    // Store
    public function store(AddItemSatuanRequest $request)
    {
      try {
        DB::beginTransaction();
        ItemSatuan::create([
          'id_admin'    => Auth::id(),
          'nama'        => $request->nama,
          'harga'       => $request->harga,
          'kategori'    => $request->kategori,
          'status'      => 'Active'
        ]);

        DB::commit();
        Session::flash('success','Item Satuan Berhasil Ditambah !');
        return redirect('item-satuan');
      } catch (ErrorException $e) {
        DB::rollback();
        throw new ErrorException($e->getMessage());
      }
    }

    // Create
    public function edit($id)
    {
      $item = ItemSatuan::find($id);
      return view('modul_admin.item_satuan.edit', compact('item'));
    }

    // Create
    public function show($id)
    {
      $item = ItemSatuan::find($id);
      return view('modul_admin.item_satuan.edit', compact('item'));
    }

    // Update 
    public function update(Request $request)
    {
      $itemSatuan = ItemSatuan::find($request->id);
      $itemSatuan->update([
          'nama'        => $request->nama,
          'harga'       => $request->harga,
          'kategori'    => $request->kategori,
          'status'      => $request->status
      ]);

      Session::flash('success','Item Satuan Berhasil Diupdate.');
      return redirect('item-satuan');
    }

    public function destroy($id)
    {
        ItemSatuan::destroy($id);
        return redirect('item-satuan');
    }
}
