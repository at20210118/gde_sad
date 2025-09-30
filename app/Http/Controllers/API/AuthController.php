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
    try {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|max:255|email|unique:users,email',
            'password' => [
                'required',
                'string',
                Password::min(8)->mixedCase()->numbers()
            ],
        ], [
            'name.required' => 'Ime je obavezno.',
            'name.string' => 'Ime mora biti tekst.',
            'name.max' => 'Ime ne sme biti duže od 255 karaktera.',
            'email.required' => 'Email je obavezan.',
            'email.email' => 'Email nije validan.',
            'email.unique' => 'Ovaj email već postoji.',
            'password.required' => 'Lozinka je obavezna.',
            'password.string' => 'Lozinka mora biti tekst.',
            'password.min' => 'Lozinka mora imati najmanje 8 karaktera.',
        ]);

        if ($validator->fails()) {
            $firstError = $validator->errors()->first();

            return response()->json([
                'message' => $firstError,
            ], 422);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Uspešna registracija',
            'data' => $user,
            'access_token' => $token,
            'token_type' => 'Bearer',
        ]);

    } catch (\Throwable $e) {
        return response()->json([
            'error' => true,
            'message' => 'Greška tokom registracije.',
            'details' => $e->getMessage(),
        ], 500);
    }
}

    public function login(Request $request)
    {
        try{
        if (!Auth::attempt($request->only('email', 'password'))) 
	  {
            return response()->json(['message' => 'Pogrešna lozinka ili email'], 401);
        }

        $user = User::where('email', $request['email'])->firstOrFail();

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json(['message' => 'Ćao, '. $user->name, 'access_token' => $token, 'token_type' =>'Bearer', 'user' => ['role' => $user->role,  ]
    ]);

    } catch(\Throwable $e) {
            return response()->json([
                'error' => true,
                'message' => 'Greška tokom prijave.',
                'details' => $e->getMessage(),
            ], 500);
        }
    }


    function logout(Request $request){
        try{
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message'=>'Uspešno ste se izlogovali'],200);
        } catch (\Throwable $e) {
            return response()->json([
                'error' => true,
                'message' => 'Greška tokom odjavljivanja.',
                'details' => $e->getMessage(),
            ], 500);
        }
    }
    public function changePassword(Request $request)
    {
        try{
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
    } catch (\Throwable $e) {
            return response()->json([
                'error' => true,
                'message' => 'Greska pri promeni lozinke.',
                'details' => $e->getMessage(),
            ], 500);
        }
    }
}
