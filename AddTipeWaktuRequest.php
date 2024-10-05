<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AddTipeWaktuRequest extends FormRequest
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
          'nama'                  => 'required|max:50',
          'waktu'                 => 'required|max:50',
        ];
    }

    public function messages()
    {
      return [
        'name.required'                 => 'Nama tidak boleh kosong.',
        'waktu.required'                => 'Waktu tidak boleh kosong.',
      ];
    }
}
