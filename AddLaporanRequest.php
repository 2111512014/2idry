<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AddLaporanRequest extends FormRequest
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
          'keterangan'         => 'required',
          'nominal'            => 'required',
          'tgl'                => 'required',
        ];
    }

    public function messages()
    {
      return [
        'keterangan.required'          => 'Keterangan tidak boleh kosong.',
        'nominal.required'             => 'Nominal tidak boleh kosong.',
        'tgl.required'                 => 'Tanggal tidak boleh kosong.',
      ];
    }
}
