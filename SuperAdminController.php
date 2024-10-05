<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Session;

class SuperAdminController extends Controller
{
    // Halaman admin
    public function adm()
    {
      $sadm = User::where('auth','Super Admin')->get();
      return view('super_admin.pengguna.admin', compact('sadm'));
    }

    // Profile
    public function profile()
    {
      $profile = User::where('id',Auth::id())->first();
      return view('super_admin.setting.profile', compact('profile'));
    }

    // Proses edit profile
    public function edit_profile(Request $request)
    {
      $profile = User::find($request->id_profile);
      $profile->update([
        'name'  => $request->name,
        'email'  => $request->email
      ]);

      Session::flash('success','Update Profile Berhasil');
      return $profile;
    }
}
