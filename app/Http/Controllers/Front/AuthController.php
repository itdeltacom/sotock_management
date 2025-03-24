<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Validator;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class AuthController extends Controller
{
    /**
     * Show the login/register page.
     */
    public function showLoginRegister()
    {
        return view('site.auth.login-register');
    }

    /**
     * Handle user login.
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validator->errors()->first()
            ]);
        }

        $credentials = $request->only('email', 'password');
        
        if (Auth::attempt($credentials, $request->filled('remember'))) {
            return response()->json([
                'status' => 'success',
                'message' => 'Connexion réussie!',
                'redirect' => route('home')
            ]);
        }

        return response()->json([
            'status' => 'error',
            'message' => 'Les informations d\'identification fournies ne correspondent pas à nos enregistrements.'
        ]);
    }

    /**
     * Handle user registration.
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'phone' => 'nullable|string|max:20',
            'password' => 'required|string|min:8|confirmed',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validator->errors()->first()
            ]);
        }

        $userData = [
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => Hash::make($request->password),
        ];

        if ($request->hasFile('photo')) {
            $photo = $request->file('photo');
            $filename = time() . '.' . $photo->getClientOriginalExtension();
            $photo->move(public_path('uploads/users'), $filename);
            $userData['photo'] = 'uploads/users/' . $filename;
        }

        $user = User::create($userData);

        Auth::login($user);

        return response()->json([
            'status' => 'success',
            'message' => 'Compte créé avec succès!',
            'redirect' => route('home')
        ]);
    }

    /**
     * Log the user out.
     */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        return redirect()->route('login-register');
    }

    /**
     * Show the forgot password form.
     */
    public function showForgotPassword()
    {
        return view('site.auth.forgot-password');
    }

    /**
     * Handle send password reset link.
     */
    public function sendResetLinkEmail(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validator->errors()->first()
            ]);
        }

        $status = Password::sendResetLink(
            $request->only('email')
        );

        return $status === Password::RESET_LINK_SENT
            ? response()->json(['status' => 'success', 'message' => __($status)])
            : response()->json(['status' => 'error', 'message' => __($status)]);
    }

    /**
     * Show the password reset form.
     */
    public function showResetPassword(Request $request)
    {
        return view('site.auth.reset-password', ['token' => $request->token, 'email' => $request->email]);
    }

    /**
     * Handle password reset.
     */
    public function resetPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validator->errors()->first()
            ]);
        }

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password) {
                $user->forceFill([
                    'password' => Hash::make($password)
                ])->setRememberToken(Str::random(60));
                $user->save();
                event(new PasswordReset($user));
            }
        );

        return $status === Password::PASSWORD_RESET
            ? response()->json(['status' => 'success', 'message' => __($status), 'redirect' => route('login-register')])
            : response()->json(['status' => 'error', 'message' => __($status)]);
    }

    /**
     * Redirect to Google OAuth.
     */
    public function redirectToGoogle()
    {
        return Socialite::driver('google')->redirect();
    }

    /**
     * Handle Google callback.
     */
    public function handleGoogleCallback()
    {
        try {
            $googleUser = Socialite::driver('google')->user();
            
            $user = User::updateOrCreate([
                'email' => $googleUser->email,
            ], [
                'name' => $googleUser->name,
                'google_id' => $googleUser->id,
                'password' => Hash::make(Str::random(16))
            ]);
            
            Auth::login($user);
            
            return redirect()->route('home');
            
        } catch (\Exception $e) {
            return redirect()->route('login-register')->with('error', 'Une erreur est survenue lors de la connexion avec Google.');
        }
    }

    /**
     * Redirect to Facebook OAuth.
     */
    public function redirectToFacebook()
    {
        return Socialite::driver('facebook')->redirect();
    }

    /**
     * Handle Facebook callback.
     */
    public function handleFacebookCallback()
    {
        try {
            $facebookUser = Socialite::driver('facebook')->user();
            
            $user = User::updateOrCreate([
                'email' => $facebookUser->email,
            ], [
                'name' => $facebookUser->name,
                'facebook_id' => $facebookUser->id,
                'password' => Hash::make(Str::random(16))
            ]);
            
            Auth::login($user);
            
            return redirect()->route('home');
            
        } catch (\Exception $e) {
            return redirect()->route('login-register')->with('error', 'Une erreur est survenue lors de la connexion avec Facebook.');
        }
    }
}