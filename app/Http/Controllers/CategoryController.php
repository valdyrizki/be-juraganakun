<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Traits\ApiResponseTrait;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class CategoryController extends Controller
{
    use ApiResponseTrait;
    public function store(Request $request)
    {
        $isSuccess = true;
        $data = null;
        $msg = "Berhasil membuat kategori " . $request->category_name;
        $stsCode = 201;

        try {
            $data = Category::create([
                'category_name' => $request->category_name,
                'category_id' => $request->category_id,
                'seq' => $request->seq,
                'status' => $request->status,
                'user_create' => Auth::id()
            ]);
        } catch (Exception $e) {
            $msg = $e->getMessage();
            $isSuccess = false;
            $stsCode = 400;
        }

        return response()->json([
            'data' => $data,
            'isSuccess' => $isSuccess,
            'msg' => $msg
        ], $stsCode);
    }

    public function update(Request $request)
    {
        $isSuccess = true;
        $msg = 'Kategori berhasil diupdate';
        $stsCode = 200;

        $data = Category::find($request->category_id);
        if (!$data) {
            return response()->json([
                'isSuccess' => false,
                'msg' => 'Kategori tidak ditemukan!',
                'data' => 'ID ' . $request->category_id . ' NOT FOUND'
            ], 400);
        }

        try {
            $data->category_name = $request->category_name;
            $data->status = $request->status;
            $data->seq = $request->seq;
            $data->user_update = Auth::id();
            $data->save();
        } catch (Exception $e) {
            $msg = $e->getMessage();
            $isSuccess = false;
            $stsCode = 400;
        }

        return response()->json([
            'isSuccess' => $isSuccess,
            'msg' => $msg,
            'data' => $data,
        ], $stsCode);
    }

    public function destroy(Request $req)
    {
        $isSuccess = true;
        $msg = 'Kategori berhasil dihapus';
        $data = Category::find($req->category_id);
        if (!$data) {
            return response()->json([
                'isSuccess' => false,
                'msg' => 'Kategori tidak ditemukan!',
                'data' => 'ID ' . $req->category_id . ' NOT FOUND'
            ], 400);
        }
        $data->delete();

        return response()->json([
            'isSuccess' => $isSuccess,
            'msg' => $msg,
            'data' => $data,
        ]);
    }

    public function get()
    {
        $isSuccess = true;
        $msg = 'SUCCESS';
        $data = Category::orderBy('seq', 'ASC')->get();

        return response()->json([
            'isSuccess' => $isSuccess,
            'msg' => $msg,
            'data' => $data,
        ]);
    }

    public function getAll()
    {
        $data = Category::all();
        return $this->successResponse($data, "Get All Categories Success!");
    }

    public function getbyid(Request $req)
    {
        $isSuccess = true;
        $msg = 'SUCCESS';
        $data = Category::find($req->category_id);
        if (!$data) {
            return response()->json([
                'isSuccess' => false,
                'msg' => 'Kategori tidak ditemukan!',
                'data' => 'ID ' . $req->category_id . ' NOT FOUND'
            ], 400);
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
        if (!$data) {
            return response()->json([
                'isSuccess' => false,
                'msg' => 'Kategori tidak ditemukan!',
                'data' => 'ID ' . $category_id . ' NOT FOUND'
            ], 400);
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
        if (!$data) {
            return response()->json([
                'isSuccess' => false,
                'msg' => 'Kategori tidak ditemukan!',
                'data' => 'ID ' . $category_id . ' NOT FOUND'
            ], 400);
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
