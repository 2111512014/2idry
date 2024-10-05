<?php

namespace App\Exports;

use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\FromView;

class CustomerExport implements FromView
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function view(): View
    {
      $data = User::where('karyawan_id', Auth::id())->get();

      return view(
        'modul_admin.customer.excelExport',
        [
          'data'  => $data
        ]
      );
    }
}
