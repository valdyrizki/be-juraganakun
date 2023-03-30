<?php

namespace App\Http\Controllers;

use App\Models\ProductFile;
use Illuminate\Http\Request;

class FileController extends Controller
{
    public function get()
    {
        $isSuccess = true;
        $msg = 'SUCCESS';
        $data = ProductFile::all();

        return response()->json([
            'isSuccess' => $isSuccess,
            'msg' => $msg,
            'data' => $data,
        ]);
    }

    public function getByInvoice(Request $request)
    {
        $isSuccess = true;
        $msg = 'SUCCESS';
        $data = ProductFile::where('invoice_id',$request->invoice_id)->get();

        return response()->json([
            'isSuccess' => $isSuccess,
            'msg' => $msg,
            'data' => $data,
        ]);
    }

    public function getByProduct(Request $request)
    {
        $isSuccess = true;
        $msg = 'SUCCESS';
        $data = ProductFile::where('product_id',$request->product_id)->get();

        return response()->json([
            'isSuccess' => $isSuccess,
            'msg' => $msg,
            'data' => $data,
        ]);
    }

    public function getPreviewFile(Request $request)
    {
        $isSuccess = true;
        $msg = 'SUCCESS';
        $data = ProductFile::whereNull('invoice_id')->where("product_id",$request->product_id)->take($request->qty)->get();

        return response()->json([
            'isSuccess' => $isSuccess,
            'msg' => $msg,
            'data' => $data,
        ]);
    }
}
