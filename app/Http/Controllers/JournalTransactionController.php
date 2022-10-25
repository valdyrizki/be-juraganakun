<?php

namespace App\Http\Controllers;

use App\Models\JournalTransaction;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class JournalTransactionController extends Controller
{
    public function get()
    {
        $isSuccess = true;
        $msg = 'SUCCESS';
        $data = JournalTransaction::all();

        return response()->json([
            'isSuccess' => $isSuccess,
            'msg' => $msg,
            'data' => $data,
        ]);
    }
    
    public function store(Request $request)
    {
        $isSuccess = true;
        $data = null;
        $msg = "Berhasil membuat jurnal transaction ".$request->txid;

        try{
            $data = JournalTransaction::create([
                'txid' => $this->getTxId(),
                'journal_account_id' => $request->journal_account_id,
                'dbcr' => $request->dbcr,
                'amount' => $request->amount,
                'description' => $request->description,
                'status' => $request->status,
                'user_create' => $request->user_create
            ]);
        }catch(Exception $e){
            $msg = $e->getMessage();
            $isSuccess = false;
        }
        
        return response()->json([
            'data' => $data,
            'isSuccess' => $isSuccess,
            'msg' => $msg
        ]);
    }

    // public function update(Request $request)
    // {
    //     $isSuccess = true;
    //     $msg = 'Jurnal transaction berhasil diupdate';

    //     $data = JournalTransaction::find($request->id);
    //     if(! $data){
    //         return response()->json([
    //             'isSuccess' => false,
    //             'msg' => 'Jurnal transaction tidak ditemukan!',
    //             'data' => 'ID '.$request->id.' NOT FOUND'
    //         ]);
    //     }
    //     $data->journal_account_id = $request->journal_account_id != null ? $request->journal_account_id : $data->journal_account_id;
    //     $data->dbcr = $request->dbcr != null ? $request->dbcr : $data->dbcr;
    //     $data->amount = $request->amount != null ? $request->amount : $data->amount;
    //     $data->description = $request->description != null ? $request->description : $data->description;
    //     $data->status = $request->status != null ? $request->status : $data->status;
    //     $data->user_create = $request->user_create != null ? $request->user_create : $data->user_create;
    //     $data->save(); 

    //     return response()->json([
    //         'isSuccess' => $isSuccess,
    //         'msg' => $msg,
    //         'data' => $data,
    //     ]);
    // }
    
    public function destroy(Request $request)
    {
        $isSuccess = true;
        $msg = 'Jurnal transaction berhasil dihapus';
        $data = JournalTransaction::find($request->id);
        if(! $data){
            return response()->json([
                'isSuccess' => false,
                'msg' => 'Jurnal transaction tidak ditemukan!',
                'data' => 'ID '.$request->id.' NOT FOUND'
            ]);
        }
        $data->delete();

        return response()->json([
            'isSuccess' => $isSuccess,
            'msg' => $msg,
            'data' => $data,
        ]);
    }

    public function getTxId()
    {
        $random =  Str::random(32);
        $count = JournalTransaction::where('txid',$random)->count();
        if ($count>0) {
            $random =  Str::random(32);
        }
        return $random;
    }
}
