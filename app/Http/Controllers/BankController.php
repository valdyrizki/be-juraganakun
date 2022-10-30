<?php

namespace App\Http\Controllers;

use App\Models\Bank;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class BankController extends Controller
{
    public function get()
    {
        $isSuccess = true;
        $msg = 'SUCCESS';
        $data = Bank::where('status',1)->get();

        return response()->json([
            'isSuccess' => $isSuccess,
            'msg' => $msg,
            'data' => $data,
        ]);
    }
    
    public function getAll()
    {
        $isSuccess = true;
        $msg = 'SUCCESS';
        $data = Bank::all();

        return response()->json([
            'isSuccess' => $isSuccess,
            'msg' => $msg,
            'data' => $data,
        ]);
    }

    public function getById(Request $request)
    {
        $isSuccess = true;
        $msg = 'SUCCESS';
        $data = Bank::find($request->id);

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
        $msg = "Berhasil menambah bank ".$request->name;

        try{
            $data = Bank::create([
                'name' => $request->name,
                'accnbr' => $request->accnbr,
                'url_logo' => $request->url_logo,
                'description' => $request->description,
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
        $msg = 'Bank berhasil diupdate';

        //validate
        $validator = Validator::make($request->all(), [
            'name' => 'required|max:30',
            'accnbr' => 'required|max:20',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'isSuccess' => false,
                'msg' => $validator->messages()->all(),
                'data' => $validator->messages()
            ]);
        }

        $data = Bank::find($request->id);
        if(! $data){
            return response()->json([
                'isSuccess' => false,
                'msg' => 'Bank tidak ditemukan!',
                'data' => 'ID '.$request->id.' NOT FOUND'
            ]);
        }

        $data->name = $request->name;
        $data->accnbr = $request->accnbr;
        $data->url_logo = $request->url_logo;
        $data->description = $request->description;
        $data->status = $request->status;
        $data->save(); 

        return response()->json([
            'isSuccess' => $isSuccess,
            'msg' => $msg,
            'data' => $data,
        ]);
    }

    public function destroy($id)
    {
        $isSuccess = true;
        $msg = 'Bank berhasil dihapus';
        $data = Bank::find($id);
        if(! $data){
            return response()->json([
                'isSuccess' => false,
                'msg' => 'Bank tidak ditemukan!',
                'data' => 'ID '.$id.' NOT FOUND'
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
