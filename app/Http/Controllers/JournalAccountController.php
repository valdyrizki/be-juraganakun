<?php

namespace App\Http\Controllers;

use App\Models\JournalAccount;
use Exception;
use Illuminate\Http\Request;

class JournalAccountController extends Controller
{
    public function get()
    {
        $isSuccess = true;
        $msg = 'SUCCESS';
        $data = JournalAccount::all();

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
        $msg = "Berhasil membuat jurnal account ".$request->name;

        try{
            $data = JournalAccount::create([
                'name' => $request->name,
                'balance' => $request->balance,
                'status' => $request->status
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

    public function update(Request $request)
    {
        $isSuccess = true;
        $msg = 'Jurnal account berhasil diupdate';

        $data = JournalAccount::find($request->id);
        if(! $data){
            return response()->json([
                'isSuccess' => false,
                'msg' => 'Jurnal account tidak ditemukan!',
                'data' => 'ID '.$request->id.' NOT FOUND'
            ]);
        }
        $data->name = $request->name != null ? $request->name : $data->name;
        $data->balance = $request->balance != null ? $request->balance : $data->balance;
        $data->status = $request->status != null ? $request->status : $data->status;
        $data->save(); 

        return response()->json([
            'isSuccess' => $isSuccess,
            'msg' => $msg,
            'data' => $data,
        ]);
    }
    
    public function destroy(Request $request)
    {
        $isSuccess = true;
        $msg = 'Jurnal account berhasil dihapus';
        $data = JournalAccount::find($request->id);
        if(! $data){
            return response()->json([
                'isSuccess' => false,
                'msg' => 'Jurnal account tidak ditemukan!',
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
}