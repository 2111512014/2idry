<?php

namespace App\Exports;

use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\FromView;

class AdminExport implements FromView
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function view(): View
    {
      $data = User::where('auth', 'Admin')->get();

      return view(
        'super_admin.admin.excelExport',
        [
          'data'  => $data
        ]
      );
    }
}
