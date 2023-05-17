<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserCollection;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Models\UserDetail;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
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

    public function store(Request $request)
    {
        $isError = false;
        $data = null;
        $msg = null;

        DB::beginTransaction();
        try {
            $user = User::create([
                'name' => $request->first_name,
                'email' => $request->email,
                'password' => bcrypt($request->password),
                'level' => 1,
            ]);

            UserDetail::create([
                'user_id' => $user->id,
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'phone' => $request->phone,
            ]);

            $data = $user;
            $msg = "Berhasil membuat user " . $request->email;
            DB::commit();
        } catch (Exception $e) {
            $data = $e->getMessage();
            $isError = true;
        }

        return response()->json([
            'data' => $data,
            'isError' => $isError,
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
        $msg = 'User berhasil diupdate';
        $user = User::find($request->id);
        $data = UserDetail::where('user_id', $user->id)->first();
        if (!$data) {
            return response()->json([
                'isSuccess' => false,
                'msg' => 'User tidak ditemukan!',
                'data' => 'ID ' . $request->id . ' NOT FOUND'
            ], 400);
        }

        $data->first_name = $request->first_name == null ? $data->first_name : $request->first_name;
        $data->last_name = $request->last_name == null ? $data->last_name : $request->last_name;
        $data->phone = $request->phone == null ? $data->phone : $request->phone;
        $data->save();

        return response()->json([
            'isSuccess' => $isSuccess,
            'msg' => $msg,
            'data' => new UserResource($user),
        ]);
    }

    public function updateMe(Request $request)
    {
        $isSuccess = true;
        $isChangePassword = $request->password;
        $msg = 'Update Success!';
        $user_id = Auth::id();
        $user = User::find($user_id);
        $data = UserDetail::where('user_id', $user_id)->first();
        if (!$data || !$user) {
            return response()->json([
                'isSuccess' => false,
                'msg' => 'User tidak ditemukan!',
                'data' => 'ID ' . $request->id . ' NOT FOUND'
            ], 400);
        }

        $user->email = $request->email == null ? $user->email : $request->email;
        $user->name = $request->username == null ? $user->name : $request->username;
        $user->password = $isChangePassword ? bcrypt($request->password) : $user->password;
        $user->save();

        $data->first_name = $request->first_name == null ? $data->first_name : $request->first_name;
        $data->last_name = $request->last_name == null ? $data->last_name : $request->last_name;
        $data->phone = $request->phone == null ? $data->phone : $request->phone;
        $data->save();

        $token = $request->bearerToken();
        if ($isChangePassword) {
            $user = User::find(Auth::id());
            $user->tokens()->delete();
            $token = $user->createToken('client-juraganakun')->plainTextToken;
        }


        return response()->json([
            'isSuccess' => $isSuccess,
            'msg' => $msg,
            'data' => new UserResource($user),
            'token' => $token,
        ]);

        $token = $user->createToken('client-juraganakun')->plainTextToken;

        return response()->json([
            'data' => new UserResource($user),
            'isSuccess' => $isSuccess,
            'msg' => $msg,
            'token' => $token,
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
        $msg = 'User berhasil dihapus';
        $user = User::find($request->id);
        if (!$user) {
            return response()->json([
                'isSuccess' => false,
                'msg' => 'Produk tidak ditemukan!',
                'data' => 'ID ' . $request->id . ' NOT FOUND'
            ], 400);
        }
        $user->delete();


        $data = UserDetail::where('user_id', $request->id)->first();
        $data->delete();

        return response()->json([
            'isSuccess' => $isSuccess,
            'msg' => $msg,
            'data' => $user,
        ]);
    }

    public function get()
    {
        $isSuccess = true;
        $msg = 'SUCCESS';
        $data = new UserCollection(UserResource::collection(User::all()));

        return response()->json([
            'isSuccess' => $isSuccess,
            'msg' => $msg,
            'data' => $data,
        ]);
    }

    public function getUserLogin()
    {
        $isSuccess = true;
        $msg = 'SUCCESS';
        $data = new UserResource(User::find(Auth::id()));

        return response()->json([
            'isSuccess' => $isSuccess,
            'msg' => $msg,
            'data' => $data,
        ]);
    }

    public function getById(Request $req)
    {
        $isSuccess = true;
        $msg = 'SUCCESS';
        $data = new UserResource(User::find($req->id));

        return response()->json([
            'isSuccess' => $isSuccess,
            'msg' => $msg,
            'data' => $data,
        ]);
    }

    public function setActive(Request $request)
    {
        $isSuccess = true;
        $msg = 'User berhasil diaktifkan';
        $user = User::find($request->id);
        $data = UserDetail::where('user_id', $request->id)->first();
        if (!$data) {
            return response()->json([
                'isSuccess' => false,
                'msg' => 'User tidak ditemukan!',
                'data' => 'ID ' . $request->id . ' NOT FOUND'
            ], 400);
        }
        $data->status = 9;
        $data->save();

        return response()->json([
            'isSuccess' => $isSuccess,
            'msg' => $msg,
            'data' => new UserResource($user),
        ]);
    }

    public function setDisable(Request $request)
    {
        $isSuccess = true;
        $msg = 'User berhasil dinonaktifkan';
        $user = User::find($request->id);
        $data = UserDetail::where('user_id', $request->id)->first();
        if (!$data) {
            return response()->json([
                'isSuccess' => false,
                'msg' => 'User tidak ditemukan!',
                'data' => 'ID ' . $request->id . ' NOT FOUND'
            ], 400);
        }
        $data->status = 9;
        $data->save();

        return response()->json([
            'isSuccess' => $isSuccess,
            'msg' => $msg,
            'data' => new UserResource($user),
        ]);
    }
}
