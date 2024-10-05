<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\{PageSettings,User,LaundrySetting,DataBank, LevelSetting, notifications_setting};
use Auth;
use Session;

class SettingsController extends Controller
{

  // Settings
  public function setting()
  {
    $setpage    = PageSettings::where('id_admin', Auth::id())->first();

    return view('super_admin.setting.index', compact('setpage'));
  }

  // Proses setting page
  public function proses_set_page(Request $request, $id)
  {
    $request->validate([
      'judul'   => 'required|max:15'
    ]);

    $img_hero = $request->file('img_hero');
    if ($img_hero) {
        $img_heros = time()."_".$img_hero->getClientoriginalName();
        // Folder Penyimpanan
        $tujuan_upload = 'frontend/img/logo';
        $img_hero->move($tujuan_upload, $img_heros);
    }

    $setpage = PageSettings::find($id);
    $setpage->judul     = $request->judul;
    $setpage->img_hero  = $img_hero;
    $setpage->tentang   = $request->tentang;
    $setpage->facebook  = $request->facebook;
    $setpage->instagram = $request->instagram;
    $setpage->twitter   = $request->twitter;
    $setpage->whatsapp  = $request->whatsapp;
    $setpage->no_telp   = $request->no_telp;
    $setpage->email     = $request->email;
    $setpage->save();

    if ($setpage) {
      Session::flash('success','Setting Berhasil Disimpan !');
      return back();
    }
  }

  // Check Setting Theme
  public function set_theme(Request $request)
  {
    $id = Auth::id();
    $user = User::all();

    $set_theme = User::findOrFail($id);
    if ($request->theme == NULL) {
      $set_theme->theme = '0';
    } else {
      $set_theme->theme = $request->theme;
    }

    $set_theme->save();

    Session::flash('success','Setting Berhasil Disimpan !');
    return back();
  }
}
