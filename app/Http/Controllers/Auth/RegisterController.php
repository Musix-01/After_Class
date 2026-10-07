<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class RegisterController extends Controller
{
    public function showRegistrationForm()
    {
        return view('registration');
    }

    public function register(Request $request)
    {
        $validated = $request->validate([
            'username' => 'required|string|max:20|unique:users,username',
            'birthday' => 'required|date|before:today',
            'email'    => 'required|email|max:255|unique:users,email',
            'psw'      => [
                'required',
                'string',
                'min:8',
                'regex:/[A-Z]/',          // at least one uppercase
                'regex:/[^A-Za-z0-9]/',   // at least one special char
                'confirmed',
            ],
        ], [
            'username.max'      => 'Username must be 20 characters or fewer.',
            'username.unique'   => 'That username is already taken.',
            'birthday.before'   => 'Birthday must be a date in the past.',
            'email.email'       => 'Please enter a valid email address.',
            'email.unique'      => 'That email is already registered.',
            'psw.min'           => 'Password must be at least 8 characters.',
            'psw.regex'         => 'Password needs at least one capital letter and one special character.',
            'psw.confirmed'     => 'Passwords do not match.',
        ]);

        $user = User::create([
            'name'     => $validated['username'],
            'username' => $validated['username'],
            'birthday' => $validated['birthday'],
            'email'    => $validated['email'],
            'password' => Hash::make($validated['psw']),
        ]);

        return redirect()->route('login');
    }
}
