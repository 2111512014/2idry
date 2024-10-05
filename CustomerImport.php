<?php

namespace App\Imports;

use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class CustomerImport implements ToModel, WithHeadingRow
{
    /**
    * @param Collection $collection
    */
    public function model(array $array)
    {
        $addCustomer = new User();
        $addCustomer->karyawan_id = Auth::id();
        $addCustomer->name = $array['nama'];
        $addCustomer->level = 'Bronze';
        $addCustomer->email = $array['email'];
        $addCustomer->password = Hash::make('12345678');
        $addCustomer->status = 'Active';
        $addCustomer->auth = 'Customer';
        $addCustomer->id_cabang_laundry = Auth::user()->cabangLaundryAdmin[0]->id;
        $addCustomer->alamat = $array['alamat'];
        $addCustomer->no_telp = '62' . $array['no_telp'];
        $addCustomer->theme = 0;
        $addCustomer->foto = '';
        $addCustomer->point = 0;
        $addCustomer->save();

        $addCustomer->assignRole($addCustomer->auth);
        return $addCustomer;
    }
}
