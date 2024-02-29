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

    public function store(Request $request)
    {
        $isError = false;
        $data = null;
        $msg = null;
        $stsCode = 201;

        DB::beginTransaction();
        try {
            $user = User::create([
                'name' => $request->username,
                'email' => $request->email,
                'password' => bcrypt($request->password),
                'level' => $request->level,
            ]);

            UserDetail::create([
                'user_id' => $user->id,
                'first_name' => $request->firstname,
                'last_name' => $request->lastname,
                'phone' => $request->phone,
                'status' => $request->status,
            ]);

            $data = $user;
            $msg = "Berhasil membuat user " . $request->email;
            DB::commit();
        } catch (Exception $e) {
            $msg = $e->getMessage();
            $isError = true;
            $stsCode = 400;
        }

        return response()->json([
            'data' => $data,
            'isError' => $isError,
            'msg' => $msg
        ], $stsCode);
    }

    public function update(Request $request)
    {
        $isSuccess = true;
        $stsCode = 200;
        $data = null;
        $user = User::find($request->id);
        $userDetail = UserDetail::where('user_id', $user->id)->first();
        if (!$userDetail || !$user) {
            return response()->json([
                'isSuccess' => false,
                'msg' => 'User tidak ditemukan!',
                'data' => 'ID ' . $request->id . ' NOT FOUND'
            ], 400);
        }

        DB::beginTransaction();
        try {
            $user->name = $request->name == null ? $user->name : $request->name;
            $user->level = $request->level == null ? $user->level : $request->level;
            $user->password = $request->password == null ? $user->password : bcrypt($request->password);
            $user->save();

            $userDetail->first_name = $request->firstname == null ? $userDetail->firstname : $request->firstname;
            $userDetail->last_name = $request->lastname == null ? $userDetail->lastname : $request->lastname;
            $userDetail->phone = $request->phone == null ? $userDetail->phone : $request->phone;
            $userDetail->status = $request->status == null ? $userDetail->status : $request->status;
            $userDetail->save();

            $data = new UserResource($user);
            $msg = "Berhasil update user " . $request->email;
            DB::commit();
        } catch (Exception $e) {
            $msg = $e->getMessage();
            $isSuccess = false;
            $stsCode = 400;
            DB::rollBack();
        }

        return response()->json([
            'isSuccess' => $isSuccess,
            'msg' => $msg,
            'data' => $data,
        ], $stsCode);
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
        $data = null;
        $stsCode = 200;

        if (!$user) {
            return response()->json([
                'isSuccess' => false,
                'msg' => 'User tidak ditemukan!',
                'data' => 'ID ' . $request->id . ' NOT FOUND'
            ], 400);
        }

        DB::beginTransaction();
        try {
            $user->delete();
            $data = UserDetail::where('user_id', $request->id)->first();
            $data->delete();
            DB::commit();
        } catch (Exception $e) {
            $msg = $e->getMessage();
            $isSuccess = false;
            $stsCode = 400;
            DB::rollBack();
        }

        return response()->json([
            'isSuccess' => $isSuccess,
            'msg' => $msg,
            'data' => $user,
        ], $stsCode);
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

    public function getAll()
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
