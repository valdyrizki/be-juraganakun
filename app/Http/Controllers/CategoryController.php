<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class CategoryController extends Controller
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
        $msg = "Berhasil membuat kategori ".$request->category_name;

        try{
            $data = Category::create([
                'category_name' => $request->category_name,
                'category_id' => $request->category_id,
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

    public function update(Request $request)
    {
        $isSuccess = true;
        $msg = 'Kategori berhasil diupdate';

        //validate
        $validator = Validator::make($request->all(), [
            'category_name' => 'required|max:30'
        ]);
        if ($validator->fails()) {
            return response()->json([
                'isSuccess' => false,
                'msg' => $validator->messages()->all(),
                'data' => $validator->messages()
            ]);
        }

        $data = Category::find($request->category_id);
        if(! $data){
            return response()->json([
                'isSuccess' => false,
                'msg' => 'Kategori tidak ditemukan!',
                'data' => 'ID '.$request->category_id.' NOT FOUND'
            ]);
        }
        $data->category_name = $request->category_name;
        $data->user_update = Auth::id();
        $data->save(); 

        return response()->json([
            'isSuccess' => $isSuccess,
            'msg' => $msg,
            'data' => $data,
        ]);
    }

    public function destroy($category_id)
    {
        $isSuccess = true;
        $msg = 'Kategori berhasil dihapus';
        $data = Category::find($category_id);
        if(! $data){
            return response()->json([
                'isSuccess' => false,
                'msg' => 'Kategori tidak ditemukan!',
                'data' => 'ID '.$category_id.' NOT FOUND'
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
        $data = Category::all();

        return response()->json([
            'isSuccess' => $isSuccess,
            'msg' => $msg,
            'data' => $data,
        ]);
    }

    public function getbyid(Request $req)
    {
        $isSuccess = true;
        $msg = 'SUCCESS';
        $data = Category::find($req->category_id);
        if(! $data){
            return response()->json([
                'isSuccess' => false,
                'msg' => 'Kategori tidak ditemukan!',
                'data' => 'ID '.$req->category_id.' NOT FOUND'
            ]);
        }
        return response()->json([
            'isSuccess' => $isSuccess,
            'msg' => $msg,
            'data' => $data,
        ]); 
    }

    public function setActive($category_id)
    {
        $isSuccess = true;
        $msg = 'Kategori berhasil diaktifkan';
        $data = Category::find($category_id);
        if(! $data){
            return response()->json([
                'isSuccess' => false,
                'msg' => 'Kategori tidak ditemukan!',
                'data' => 'ID '.$category_id.' NOT FOUND'
            ]);
        }
        $data->status = 1;
        $data->save();

        return response()->json([
            'isSuccess' => $isSuccess,
            'msg' => $msg,
            'data' => $data,
        ]);
    }

    public function setDisable($category_id)
    {
        $isSuccess = true;
        $msg = 'Kategori berhasil dinonaktifkan';
        $data = Category::find($category_id);
        if(! $data){
            return response()->json([
                'isSuccess' => false,
                'msg' => 'Kategori tidak ditemukan!',
                'data' => 'ID '.$category_id.' NOT FOUND'
            ]);
        }
        $data->status = 9;
        $data->save();

        return response()->json([
            'isSuccess' => $isSuccess,
            'msg' => $msg,
            'data' => $data,
        ]);
    }
}
