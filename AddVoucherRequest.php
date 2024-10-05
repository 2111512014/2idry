<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AddVoucherRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
          'nama'                        => 'required|max:25',
          'diskon'                      => 'required',
          'tgl_berlaku'                 => 'required',
          'tgl_berakhir'                => 'required',
          'jenis_layanan'               => 'required',
          'level'                       => 'required',
        ];
    }

    public function messages()
    {
      return [
        'name.required'                 => 'Nama tidak boleh kosong.',
        'name.max'                      => 'Nama tidak boleh lebih dari 50 karakter.',
        'diskon.required'               => 'Diskon tidak boleh kosong.',
        'tgl_berlaku.required'          => 'Tanggal berlaku tidak boleh kosong.',
        'tgl_berakhir.required'         => 'Tanggal berakhir tidak boleh kosong.',
        'jenis_layanan.required'        => 'Jenis Layanan tidak boleh kosong.',
        'level.required'                => 'Level tidak boleh kosong.',
      ];
    }
}
