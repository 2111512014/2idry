<?php

namespace App\Http\Controllers\Karyawan;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\{transaksi,DataBank};
use charlieuki\ReceiptPrinter\ReceiptPrinter as ReceiptPrinter;
use Auth;
use PDF;
class InvoiceController extends Controller
{
       // Invoice
    public function invoicekar(Request $request)
    {
      $invoice = transaksi::where('user_id',Auth::id())
      ->where('invoice',$request->invoice)
      ->get();

      $data = transaksi::with('customers','user')
      ->where('user_id',Auth::id())
      ->where('invoice',$request->invoice)
      ->first();

      $bank = DataBank::get();
      return view('karyawan.laporan.invoice', compact('invoice','data','bank'));
    }

    // Cetak invoice
    public function cetakinvoice(Request $request)
    {
      $invoice = transaksi::where('user_id',Auth::id())
      ->where('invoice',$request->invoice)
      ->get();

      $data = transaksi::with('customers','user')
      ->where('user_id',Auth::id())
      ->where('invoice',$request->invoice)
      ->first();

      $bank = DataBank::get();

      $pdf = PDF::loadView('karyawan.laporan.cetak', compact('invoice','data','bank'))->setPaper('a4', 'potrait');
      return $pdf->stream();
    }

    public function cetakreceipt(Request $request)
    {
      $invoice = transaksi::where('user_id',Auth::id())
      ->where('invoice',$request->invoice)
      ->get();

      $data = transaksi::with('customers','user')
      ->where('user_id',Auth::id())
      ->where('invoice',$request->invoice)
      ->first();

        // Set params
        $mid = '123123456';
        $store_name = $data->cabangLaundry->nama;
        $store_address = $data->cabangLaundry->alamat;
        $store_phone = Auth::user()->no_telp;
        $store_email = Auth::user()->email;
        $transaction_id = $request->invoice;
        $tax_percentage = 10;
        $store_website = '';

        // Init printer
        $printer = new ReceiptPrinter;
        $printer->init(
            config('receiptprinter.connector_type'),
            config('receiptprinter.connector_descriptor')
        );

        // Set store info
        $printer->setStore($mid, $store_name, $store_address, $store_phone, $store_email, $store_website);

        // Add items
        foreach ($invoice as $item) {
          if($item->id_cuci_kiloan != null){
            $printer->addItem(
                $item->cuciKiloan->nama,
                $item->kg,
                $item->harga,
            );
          }else{
            $printer->addItem(
                $item->itemSatuan->nama,
                $item->kg,
                $item->harga,
            );
          }
        }
        // Set tax
        $printer->setTax($tax_percentage);

        // Calculate total
        $printer->calculateSubTotal();
        $printer->calculateGrandTotal();

        // Set transaction ID
        $printer->setTransactionID($transaction_id);

        // Set qr code
        $printer->setQRcode([
            'tid' => $transaction_id,
        ]);

        // Print receipt
        $printer->printReceipt();
    }
}
