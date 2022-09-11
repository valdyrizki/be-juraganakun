<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class SettingController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $isSuccess = true;
        $data = null;
        $msg = "Berhasil membuat pengaturan ".$request->setting_name;

        //validate
        $validator = Validator::make($request->all(), [
            'setting_id' => 'required|max:12',
            'setting_name' => 'required',
            'setting_value' => 'required'
        ]);
        if ($validator->fails()) {
            return response()->json([
                'isSuccess' => false,
                'msg' => $validator->messages()->all(),
                'data' => $validator->messages()
            ]);
        }

        try{
            $data = Setting::create([
                'setting_id' => $request->setting_id,
                'setting_name' => $request->setting_name,
                'setting_value' => $request->setting_value,
                'user_create' => Auth::id()
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

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        $isSuccess = true;
        $msg = 'Pengaturan berhasil diupdate';

        //validate
        $validator = Validator::make($request->all(), [
            'setting_id' => 'required|max:12',
            'setting_value' => 'required'
        ]);
        if ($validator->fails()) {
            return response()->json([
                'isSuccess' => false,
                'msg' => $validator->messages()->all(),
                'data' => $validator->messages()
            ]);
        }

        $data = Setting::find($request->setting_id);
        if(! $data){
            return response()->json([
                'isSuccess' => false,
                'msg' => 'Pengaturan tidak ditemukan!',
                'data' => 'ID '.$request->setting_id.' NOT FOUND'
            ]);
        }
        $data->setting_name = $request->setting_name != null ? $request->setting_name : $data->setting_name;
        $data->setting_value = $request->setting_value;
        $data->user_update = Auth::id();
        $data->save(); 

        return response()->json([
            'isSuccess' => $isSuccess,
            'msg' => $msg,
            'data' => $data,
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        $isSuccess = true;
        $msg = 'Pengaturan berhasil dihapus';
        $data = Setting::find($request->setting_id);
        if(! $data){
            return response()->json([
                'isSuccess' => false,
                'msg' => 'Pengaturan tidak ditemukan!',
                'data' => 'ID '.$request->setting_id.' NOT FOUND'
            ]);
        }
        $data->delete();

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
        $data = Setting::all();

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
        $data = Setting::find($request->setting_id);
        if(! $data){
            return response()->json([   
                'isSuccess' => false,
                'msg' => 'Pengaturan tidak ditemukan!',
                'data' => 'ID '.$request->setting_id.' NOT FOUND'
            ]);
        }
        return response()->json([
            'isSuccess' => $isSuccess,
            'msg' => $msg,
            'data' => $data,
        ]); 
    }
}
