<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;


class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'=>'required|string|max:255',
            'email' => 'required|string|max:255|email|unique:users',
            'password' => [
                'required',
                'string',
                Password::min(8)->mixedCase()->numbers()
            ],
        ]);
        if ($validator->fails())
        return response()->json($validator->errors());

    $user = User::create([
        'name' => $request->name,
        'email' => $request->email,
        'password' => Hash::make($request->password),
    ]);

    $token = $user->createToken('auth_token')->plainTextToken;

    return response()
    ->json(['data' => $user, 'access_token' => $token,'token_type' => 'Bearer',]);
    }


    public function login(Request $request)
    {
        if (!Auth::attempt($request->only('email', 'password'))) 
	  {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $user = User::where('email', $request['email'])->firstOrFail();

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json(['message' => 'Hi '. $user->name, 'access_token' => $token, 'token_type' =>'Bearer',]);
    }


    function logout(Request $request){
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message'=>'You have been successfully logged out'],200);
    }
    public function changePassword(Request $request)
    {
    $request->validate([
        'current_password' => 'required|string',
        'new_password' => 'required|string|min:8|confirmed',
    ]);

    $user = $request->user();

    if (!\Hash::check($request->current_password, $user->password)) {
        return response()->json(['error' => 'Trenutna lozinka nije ispravna.'], 403);
    }

    $user->password = bcrypt($request->new_password);
    $user->save();

    return response()->json(['message' => 'Lozinka uspešno promenjena.']);
    }

}
