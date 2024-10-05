<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\VoucherCustomer;
use Illuminate\Support\Facades\Auth;

class VoucherController  extends Controller
{
    public function index()
    {
      $voucher = VoucherCustomer::with('voucher', 'customer')->where('id_customer', Auth::id())->get();
      return view('customer.voucher.index', compact('voucher'));
    }

    public function claimVoucher(Request $request)
    {
      VoucherCustomer::create([
        'id_customer' => Auth::id(),
        'id_voucher' => $request->id
      ]);
      return 1;
    }
}
