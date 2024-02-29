<?php

namespace App\Http\Controllers;

use App\Models\Role;
use Illuminate\Http\Request;

class RoleController extends Controller
{

    public function get()
    {
        $isSuccess = true;
        $msg = 'SUCCESS';
        $data = Role::all();

        return response()->json([
            'isSuccess' => $isSuccess,
            'msg' => $msg,
            'data' => $data,
        ]);
    }

    // public function store(Request $request)
    // {
    //     $isError = false;
    //     $data = null;
    //     $msg = null;

    //     DB::beginTransaction();
    //     try {
    //         $user = User::create([
    //             'name' => $request->username,
    //             'email' => $request->email,
    //             'password' => bcrypt($request->password),
    //             'level' => 1,
    //         ]);

    //         UserDetail::create([
    //             'user_id' => $user->id,
    //             'first_name' => $request->firstname,
    //             'last_name' => $request->lastname,
    //             'phone' => $request->phone,
    //         ]);

    //         $data = $user;
    //         $msg = "Berhasil membuat user " . $request->email;
    //         DB::commit();
    //     } catch (Exception $e) {
    //         $msg = $e->getMessage();
    //         $isError = true;
    //     }

    //     return response()->json([
    //         'data' => $data,
    //         'isError' => $isError,
    //         'msg' => $msg
    //     ]);
    // }

    // public function update(Request $request)
    // {
    //     $isSuccess = true;
    //     $msg = 'User berhasil diupdate';
    //     $user = User::find($request->id);
    //     $data = UserDetail::where('user_id', $user->id)->first();
    //     if (!$data) {
    //         return response()->json([
    //             'isSuccess' => false,
    //             'msg' => 'User tidak ditemukan!',
    //             'data' => 'ID ' . $request->id . ' NOT FOUND'
    //         ], 400);
    //     }

    //     $data->first_name = $request->firstname == null ? $data->firstname : $request->firstname;
    //     $data->last_name = $request->lastname == null ? $data->lastname : $request->lastname;
    //     $data->phone = $request->phone == null ? $data->phone : $request->phone;
    //     $data->status = $request->status == null ? $data->status : $request->status;
    //     $data->save();

    //     return response()->json([
    //         'isSuccess' => $isSuccess,
    //         'msg' => $msg,
    //         'data' => new UserResource($user),
    //     ]);
    // }

    // public function destroy(Request $request)
    // {
    //     $isSuccess = true;
    //     $msg = 'User berhasil dihapus';
    //     $user = User::find($request->id);
    //     if (!$user) {
    //         return response()->json([
    //             'isSuccess' => false,
    //             'msg' => 'Produk tidak ditemukan!',
    //             'data' => 'ID ' . $request->id . ' NOT FOUND'
    //         ], 400);
    //     }
    //     $user->delete();


    //     $data = UserDetail::where('user_id', $request->id)->first();
    //     $data->delete();

    //     return response()->json([
    //         'isSuccess' => $isSuccess,
    //         'msg' => $msg,
    //         'data' => $user,
    //     ]);
    // }
}
