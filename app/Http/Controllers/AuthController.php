<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserDetail;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $isError = false;
        $data = null;
        $msg = null;

        try{
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => bcrypt($request->password),
                'level' => 1,
            ]);

            UserDetail::create([
                'user_id' => $user->id
            ]);

            $data = $user;
            $msg = "Berhasil membuat user ".$request->email;
        }catch(Exception $e){
            
            $data = $e->getMessage();
            $isError = true;
        }
        
        return response()->json([
            'data' => $data,
            'isError' => $isError,
            'msg' => $msg
        ]);
    }

    public function login(Request $request)
    {
        $isSuccess = true;
        $msg = null;

        $user= User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response([
                'msg' => ['These credentials do not match our records.']
            ]);
        }

        $token = $user->createToken('juraganakun-token')->plainTextToken;

        return response()->json([
            'data' => $user,
            'isSuccess' => $isSuccess,
            'msg' => $msg,
            'token' => $token,
        ]);
    }
}
