<?php

namespace App\Http\Controllers\Admin\Auth;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use Carbon\Carbon;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    /**
     * Show the login form
     */
    public function showLoginForm()
    {
        return view('admin.auth.login');
    }
    
    /**
     * Handle admin login request
     */
    public function login(Request $request)
    {
        // Real-time validation via AJAX
        if ($request->ajax()) {
            $validator = Validator::make($request->all(), [
                'email' => 'required|email',
                'password' => 'required',
            ]);
            
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            
            return response()->json(['success' => true]);
        }
        
        // Regular form submission
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);
        
        if (Auth::guard('admin')->attempt($credentials, $request->boolean('remember'))) {
            $admin = Auth::guard('admin')->user();
            
            // Check if admin is active
            if (!$admin->is_active) {
                Auth::guard('admin')->logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
                
                return back()->withErrors([
                    'email' => 'Your account has been deactivated. Please contact the system administrator.',
                ]);
            }
            
            // Update last login information
            $admin->update([
                'last_login_at' => now(),
                'last_login_ip' => $request->ip(),
            ]);
            
            $request->session()->regenerate();
            
            // Check for two-factor authentication
            if ($admin->two_factor_enabled) {
                return redirect()->route('admin.two-factor.verify');
            }
            
            return redirect()->intended(route('admin.dashboard'));
        }
        
        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }
    
    /**
     * Handle admin logout request
     */
    public function logout(Request $request)
    {
        Auth::guard('admin')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        return redirect()->route('admin.login');
    }
    
    /**
     * Show the forgot password form
     */
    public function showForgotPasswordForm()
    {
        return view('admin.auth.forgot-password');
    }
    
    /**
     * Handle forgot password request
     */
    public function forgotPassword(Request $request)
    {
        // Real-time validation via AJAX
        if ($request->ajax()) {
            $validator = Validator::make($request->all(), [
                'email' => 'required|email|exists:admins,email',
            ]);
            
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            
            return response()->json(['success' => true]);
        }
        
        // Regular form submission
        $request->validate([
            'email' => 'required|email|exists:admins,email',
        ]);
        
        // Delete any existing reset tokens for this email
        DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->delete();
        
        // Generate a new token
        $token = Str::random(64);
        
        // Store the new token
        DB::table('password_reset_tokens')->insert([
            'email' => $request->email,
            'token' => Hash::make($token),
            'created_at' => Carbon::now()
        ]);
        
        // Create reset link
        $resetLink = route('admin.password.reset', ['token' => $token, 'email' => $request->email]);
        
        // Send email with reset link
        try {
            Mail::send('admin.emails.reset-password', ['resetLink' => $resetLink], function($message) use ($request) {
                $message->to($request->email);
                $message->subject('Reset Your BATI Car Rental Admin Password');
            });
            
            if ($request->ajax()) {
                return response()->json([
                    'success' => true, 
                    'message' => 'We have emailed your password reset link!'
                ]);
            }
            
            return back()->with('status', 'We have emailed your password reset link!');
            
        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json([
                    'errors' => ['email' => ['Could not send reset link. Please try again later.']]
                ], 422);
            }
            
            return back()->withErrors(['email' => 'Could not send reset link. Please try again later.']);
        }
    }
    
    /**
     * Show the reset password form
     */
    public function showResetPasswordForm(Request $request, string $token)
    {
        $email = $request->email;
        
        // Verify token is valid
        $tokenData = DB::table('password_reset_tokens')
            ->where('email', $email)
            ->first();
            
        if (!$tokenData || !Hash::check($token, $tokenData->token)) {
            return redirect()->route('admin.password.request')
                ->withErrors(['email' => 'Invalid or expired password reset token.']);
        }
        
        // Check if token is expired (tokens valid for 60 minutes)
        $tokenCreatedAt = Carbon::parse($tokenData->created_at);
        if (Carbon::now()->diffInMinutes($tokenCreatedAt) > 60) {
            DB::table('password_reset_tokens')
                ->where('email', $email)
                ->delete();
                
            return redirect()->route('admin.password.request')
                ->withErrors(['email' => 'Password reset token has expired. Please request a new link.']);
        }
        
        return view('admin.auth.reset-password', ['token' => $token, 'email' => $email]);
    }
    
    /**
     * Handle reset password request with real-time validation
     */
    public function resetPassword(Request $request)
    {
        // Real-time validation via AJAX
        if ($request->ajax()) {
            $validator = Validator::make($request->all(), [
                'token' => 'required',
                'email' => 'required|email|exists:admins,email',
                'password' => [
                    'required',
                    'string',
                    'min:8',
                    'confirmed',
                    'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/'
                ],
            ], [
                'password.regex' => 'Password must contain at least one uppercase letter, one lowercase letter, and one number.'
            ]);
            
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            
            // Verify token is valid
            $tokenData = DB::table('password_reset_tokens')
                ->where('email', $request->email)
                ->first();
                
                if (!$tokenData || !Hash::check($request->token, $tokenData->token)) {
                    return response()->json([
                        'errors' => ['email' => ['Invalid or expired password reset token.']]
                    ], 422);
                }
                
                // Check if token is expired (tokens valid for 60 minutes)
                $tokenCreatedAt = Carbon::parse($tokenData->created_at);
                if (Carbon::now()->diffInMinutes($tokenCreatedAt) > 60) {
                    DB::table('password_reset_tokens')
                        ->where('email', $request->email)
                        ->delete();
                        
                    return response()->json([
                        'errors' => ['email' => ['Password reset token has expired. Please request a new link.']]
                    ], 422);
                }
                
                return response()->json(['success' => true]);
            }
            
            // Regular form submission
            $request->validate([
                'token' => 'required',
                'email' => 'required|email|exists:admins,email',
                'password' => [
                    'required',
                    'string',
                    'min:8',
                    'confirmed',
                    'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/'
                ],
            ], [
                'password.regex' => 'Password must contain at least one uppercase letter, one lowercase letter, and one number.'
            ]);
            
            // Verify token is valid
            $tokenData = DB::table('password_reset_tokens')
                ->where('email', $request->email)
                ->first();
                
            if (!$tokenData || !Hash::check($request->token, $tokenData->token)) {
                if ($request->ajax()) {
                    return response()->json([
                        'errors' => ['email' => ['Invalid or expired password reset token.']]
                    ], 422);
                }
                
                return redirect()->route('admin.password.request')
                    ->withErrors(['email' => 'Invalid or expired password reset token.']);
            }
            
            // Update the admin's password
            $admin = Admin::where('email', $request->email)->first();
            $admin->update([
                'password' => Hash::make($request->password)
            ]);
            
            // Delete the token
            DB::table('password_reset_tokens')
                ->where('email', $request->email)
                ->delete();
            
            // Fire the password reset event
            event(new PasswordReset($admin));
            
            // For AJAX requests
            if ($request->ajax()) {
                // Log the admin in automatically for AJAX requests
                Auth::guard('admin')->login($admin);
                
                return response()->json([
                    'success' => true,
                    'message' => 'Your password has been reset successfully!',
                    'redirect' => route('admin.dashboard')
                ]);
            }
            
            // Log the admin in automatically
            Auth::guard('admin')->login($admin);
            
            return redirect()->route('admin.dashboard')
                ->with('status', 'Your password has been reset successfully!');
        }
        
        /**
         * Validate password in real-time
         */
        public function validatePassword(Request $request)
        {
            $validator = Validator::make($request->all(), [
                'password' => [
                    'required',
                    'string',
                    'min:8',
                    'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/'
                ],
            ], [
                'password.regex' => 'Password must contain at least one uppercase letter, one lowercase letter, and one number.'
            ]);
            
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            
            // Calculate password strength (0-100)
            $strength = 0;
            $password = $request->password;
            
            // Basic requirements (length)
            if (strlen($password) >= 8) $strength += 25;
            if (strlen($password) >= 12) $strength += 15;
            
            // Complexity
            if (preg_match('/[A-Z]/', $password)) $strength += 10;
            if (preg_match('/[a-z]/', $password)) $strength += 10;
            if (preg_match('/[0-9]/', $password)) $strength += 10;
            if (preg_match('/[^A-Za-z0-9]/', $password)) $strength += 15;
            
            // Variety
            $chars = str_split($password);
            $uniqueChars = count(array_unique($chars));
            $uniqueRatio = $uniqueChars / strlen($password);
            $strength += round($uniqueRatio * 15);
            
            return response()->json([
                'success' => true,
                'strength' => min(100, $strength),
                'feedback' => $this->getPasswordFeedback($strength)
            ]);
        }
        
        /**
         * Get password strength feedback
         */
        private function getPasswordFeedback($strength)
        {
            if ($strength < 40) {
                return [
                    'level' => 'weak',
                    'message' => 'Weak password. Add more variety and length.',
                    'color' => '#EF4444'
                ];
            } else if ($strength < 70) {
                return [
                    'level' => 'medium',
                    'message' => 'Medium strength. Consider adding special characters.',
                    'color' => '#FBBF24'
                ];
            } else {
                return [
                    'level' => 'strong',
                    'message' => 'Strong password. Good job!',
                    'color' => '#34D399'
                ];
            }
        }
    }