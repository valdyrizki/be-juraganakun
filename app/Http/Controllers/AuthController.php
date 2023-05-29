<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserResource;
use App\Models\User;
use App\Models\UserDetail;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $isSuccess = true;
        $data = null;
        $msg = 'Register Failed!';
        $stscode = 201;

        DB::beginTransaction();

        try {
            $user = User::create([
                'name' => $request->username,
                'email' => $request->email,
                'password' => bcrypt($request->password),
                'level' => '00',
            ]);

            UserDetail::create([
                'user_id' => $user->id,
                'phone' => $request->phone,
                'first_name' => $request->firstname,
                'last_name' => $request->lastname,
            ]);

            $data = $user;
            $msg = "Berhasil membuat user " . $request->email;
        } catch (Exception $e) {

            DB::rollBack();
            $data = $e->getMessage();
            $isSuccess = false;
            $stscode = 400;
        }

        DB::commit();

        return response()->json([
            'data' => $data,
            'isSuccess' => $isSuccess,
            'msg' => $msg
        ], $stscode);
    }

    public function login(Request $request)
    {
        $isSuccess = true;
        $msg = "Login Successfully";

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response([
                'msg' => ['These credentials do not match our records.']
            ], 401);
        }

        $token = $user->createToken('client-juraganakun')->plainTextToken;

        return response()->json([
            'data' => new UserResource($user),
            'isSuccess' => $isSuccess,
            'msg' => $msg,
            'token' => $token,
        ]);
    }

    public function loginAdmin(Request $request)
    {
        $isSuccess = true;
        $msg = "Login Successfully";

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password) || ($user->level != '10' && $user->level != '99')) {
            return response([
                'msg' => ['These credentials do not match our records.']
            ], 401);
        }

        $token = $user->createToken('client-juraganakun')->plainTextToken;

        return response()->json([
            'data' => new UserResource($user),
            'isSuccess' => $isSuccess,
            'msg' => $msg,
            'token' => $token,
        ]);
    }

    public function checkPassword(Request $request)
    {
        $isSuccess = true;
        $msg = "Verification Success";

        $user = User::find(Auth::id());

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response([
                'msg' => ['These credentials do not match our records.']
            ], 401);
        }

        $token = $user->createToken('client-juraganakun')->plainTextToken;

        return response()->json([
            'data' => new UserResource($user),
            'isSuccess' => $isSuccess,
            'msg' => $msg,
            'token' => $token,
        ]);
    }
}
