<?php

namespace App\Http\Controllers;

use App\Models\JournalCategory;
use Exception;
use Illuminate\Http\Request;

class JournalCategoryController extends Controller
{
    public function get()
    {
        $isSuccess = true;
        $msg = 'SUCCESS';
        $data = JournalCategory::all();

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
        $msg = "Berhasil membuat kategori jurnal " . $request->name;

        try {
            $data = JournalCategory::create([
                'name' => $request->name,
                'dbcr' => $request->dbcr,
                'status' => $request->status
            ]);
        } catch (Exception $e) {
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

        $data = JournalCategory::find($request->id);
        if (!$data) {
            return response()->json([
                'isSuccess' => false,
                'msg' => 'Kategori tidak ditemukan!',
                'data' => 'ID ' . $request->id . ' NOT FOUND'
            ], 400);
        }
        $data->name = $request->name != null ? $request->name : $data->name;
        $data->dbcr = $request->dbcr != null ? $request->dbcr : $data->dbcr;
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
        $msg = 'Kategori berhasil dihapus';
        $data = JournalCategory::find($request->id);
        if (!$data) {
            return response()->json([
                'isSuccess' => false,
                'msg' => 'Kategori tidak ditemukan!',
                'data' => 'ID ' . $request->id . ' NOT FOUND'
            ], 400);
        }
        $data->delete();

        return response()->json([
            'isSuccess' => $isSuccess,
            'msg' => $msg,
            'data' => $data,
        ]);
    }
}
