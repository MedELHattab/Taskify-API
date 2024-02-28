<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Repositories\UserRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;

class AuthController extends Controller
{


    protected $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function registerForm()
    {
        return view('auth.register');
    }

    public function loginForm()
    {
        return view('auth.login');
    }

    
    public function login(Request $request)
    {
        $inputs = $request->only('email', 'password');

        $user = $this->userRepository->findByEmail($inputs['email']);

        if (!$user || !password_verify($inputs['password'], $user->password)) {
            return response()->json([
                'status' => false,
                'error' => 'Invalid inputs'
            ], 401);
        }

        Auth::login($user);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'status' => true,
            'message' => 'Login successful',
            'user' => $user,
            'token' => $token
        ]);

        // $form = $request->validated();
        // if (Auth::attempt($form)) {
        //     $request->session()->regenerate();
        //     return redirect('/');
        // }
        // return back()->onlyInput('email');
    }

    
    public function register(Request $request)
    {
        $rules = [
            'name' => 'required|string',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
        ];

        $validator = validator($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json([
                "status" => false,
                'error' => $validator->errors()
            ], 400);
        }

        $user = $this->userRepository->create([
            "name" => $request->input('name'),
            "email" => $request->input('email'),
            "password" => Hash::make($request->input('password')),
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            "name" => $user->name,
            "email" => $user->email,
            "password" => $request->input('password'),
            "token" => $token
        ]);
    }

    public function logout(Request $request)
    {
        auth()->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    }

    public function showForgotPasswordForm()
    {
        return view('auth.forgot-password');
    }

    public function sendResetLinkEmail(Request $request)
    {
        $request->validate(['email' => 'required|email']);
        $status = Password::sendResetLink($request->only('email'));
        return $status === Password::RESET_LINK_SENT
            ? back()->with(['status' => $status])
            : back()->withErrors(['email' => $status]);
    }

    public function showResetPasswordForm($token)
    {
        return view('auth.reset-password', ['token' => $token]);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|confirmed|min:8',
        ]);
        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill([
                    'password' => bcrypt($password),
                ])->save();
            }
        );
        return $status == Password::PASSWORD_RESET
            ? redirect()->route('login')->with('status', $status)
            : back()->withErrors(['email' => $status]);
    }
}
