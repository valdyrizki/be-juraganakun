<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Models\UserDetail;
use App\Traits\ApiResponseTrait;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    use ApiResponseTrait;
    public function register(RegisterRequest $request)
    {
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
        } catch (Exception $e) {
            DB::rollBack();
            return $this->errorResponse("Error when Register, please contact admin support by WA : +6283818213645", false, 400);
        }

        DB::commit();
        return $this->successResponse($user, "Register Success!");
    }

    public function login(LoginRequest $request)
    {
        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return $this->errorResponse("Login Invalid", false, 401);
        }

        $token = $user->createToken('client-juraganakun')->plainTextToken;

        return response()->json([
            'data' => new UserResource($user),
            'success' => true,
            'message' => "Login Success!",
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
