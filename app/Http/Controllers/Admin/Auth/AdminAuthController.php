<?php

namespace App\Http\Controllers\Admin\Auth;

use Carbon\Carbon;
use App\Models\Admin;
use App\Facades\Alert;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Validator;

class AdminAuthController extends Controller
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
        // Validate input fields
        $validator = Validator::make($request->all(), [
            'login' => 'required',
            'password' => 'required',
        ]);
        
        if ($validator->fails()) {
            if ($request->ajax()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            return back()->withErrors($validator)->withInput();
        }
        
        // Log the request for debugging
        Log::info('Login attempt', ['login' => $request->login]);
        
        // Determine if input is email or phone number
        $loginType = filter_var($request->login, FILTER_VALIDATE_EMAIL) ? 'email' : 'phone';
        $credentials = [
            $loginType => $request->login,
            'password' => $request->password
        ];
        
        // Log the credentials and guard for debugging
        Log::info('Login credentials', ['type' => $loginType, 'guard' => 'admin']);
        
        if (Auth::guard('admin')->attempt($credentials, $request->boolean('remember'))) {
            $admin = Auth::guard('admin')->user();
            
            // Log successful login
            Log::info('Login successful', ['admin_id' => $admin->id]);
            
            // Check if admin is active
            if (!$admin->is_active) {
                Auth::guard('admin')->logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
                
                Log::warning('Inactive admin account login attempt', ['admin_id' => $admin->id]);
                
                if ($request->ajax()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Your account has been deactivated. Please contact the system administrator.'
                    ], 403);
                }
                
                return back()->withErrors([
                    'login' => 'Your account has been deactivated. Please contact the system administrator.',
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
                // Log 2FA requirement
                Log::info('2FA verification required', ['admin_id' => $admin->id]);
                
                if ($request->ajax()) {
                    // Generate redirect URL and log it for debugging
                    $redirectUrl = route('admin.two-factor.verify');
                    Log::info('2FA redirect URL', ['url' => $redirectUrl]);
                    
                    return response()->json([
                        'success' => true,
                        'message' => 'Logged in successfully. Please complete two-factor authentication.',
                        'redirect' => $redirectUrl
                    ]);
                }
                
                // Flash success message for login before redirecting to 2FA page
                session()->flash('toastr', [
                    'type' => 'success',
                    'message' => 'Please complete two-factor authentication.'
                ]);
                return redirect()->route('admin.two-factor.verify');
            }
            
            if ($request->ajax()) {
                // Generate dashboard URL and log it for debugging
                $dashboardUrl = route('admin.dashboard');
                Log::info('Dashboard redirect URL', ['url' => $dashboardUrl]);
                
                return response()->json([
                    'success' => true,
                    'message' => 'You have successfully logged in.',
                    'redirect' => $dashboardUrl
                ]);
            }
            
            // Flash success message for login
            session()->flash('toastr', [
                'type' => 'success',
                'message' => 'You have successfully logged in.'
            ]);
            
            // Redirect to dashboard with intended URL support
            return redirect()->intended(route('admin.dashboard'));
        }
        
        // Log failed login attempt
        Log::warning('Failed login attempt', ['login' => $request->login, 'ip' => $request->ip()]);
        
        if ($request->ajax()) {
            return response()->json([
                'success' => false,
                'message' => 'The provided credentials do not match our records.'
            ], 422);
        }
        
        return back()->withErrors([
            'login' => 'The provided credentials do not match our records.',
        ])->onlyInput('login');
    }
    
    /**
     * Handle admin logout request
     */
    public function logout(Request $request)
    {
        Auth::guard('admin')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        // Flash success message for logout
        session()->flash('toastr', [
            'type' => 'success',
            'message' => 'You have been logged out successfully.'
        ]);
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
    // Determine if input is email or phone number
    $loginType = filter_var($request->login, FILTER_VALIDATE_EMAIL) ? 'email' : 'phone';
    
    // Real-time validation via AJAX
    if ($request->ajax()) {
        $validator = Validator::make($request->all(), [
            'login' => 'required|exists:admins,' . $loginType,
        ]);
        
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        
        return response()->json(['success' => true]);
    }
    
    // Regular form submission
    $request->validate([
        'login' => 'required|exists:admins,' . $loginType,
    ]);
    
    // For phone numbers, fetch the associated email
    $admin = Admin::where($loginType, $request->login)->first();
    
    if (!$admin) {
        if ($request->ajax()) {
            return response()->json([
                'errors' => ['login' => ['User not found with the provided ' . $loginType . '.']]
            ], 422);
        }
        
        return back()->withErrors(['login' => 'User not found with the provided ' . $loginType . '.']);
    }
    
    $email = $admin->email;
    
    // Make sure we have a valid email
    if (empty($email)) {
        Log::error('Admin record has no email address', [
            'admin_id' => $admin->id,
            'login_type' => $loginType,
            'login' => $request->login
        ]);
        
        if ($request->ajax()) {
            return response()->json([
                'errors' => ['login' => ['No email address associated with this account. Please contact support.']]
            ], 422);
        }
        
        return back()->withErrors(['login' => 'No email address associated with this account. Please contact support.']);
    }
    
    // Delete any existing reset tokens for this email
    DB::table('password_reset_tokens')
        ->where('email', $email)
        ->delete();
    
    // Generate a new token
    $token = Str::random(64);
    
    // Store the new token
    DB::table('password_reset_tokens')->insert([
        'email' => $email,
        'token' => Hash::make($token),
        'created_at' => Carbon::now()
    ]);
    
    // Create reset link
    $resetLink = route('admin.password.reset', ['token' => $token, 'email' => $email]);
    
    // Log attempt to send email
    Log::info('Attempting to send password reset email', [
        'email' => $email,
        'reset_link' => $resetLink
    ]);
    
    // Check mail configuration
    $mailConfig = config('mail');
    Log::info('Mail configuration', [
        'driver' => $mailConfig['mailer'] ?? $mailConfig['driver'] ?? 'unknown',
        'host' => $mailConfig['host'] ?? 'unknown',
        'port' => $mailConfig['port'] ?? 'unknown',
        'from_address' => $mailConfig['from']['address'] ?? 'unknown',
        'from_name' => $mailConfig['from']['name'] ?? 'unknown'
    ]);
    
    // Send email with reset link using Mail class
    try {
        Mail::to($email)->send(new \App\Mail\ResetPasswordMail($resetLink));
        
        Log::info('Password reset email sent successfully', ['email' => $email]);
        
        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'We have emailed your password reset link!'
            ]);
        }
        
        session()->flash('toastr', [
            'type' => 'success',
            'message' => 'We have emailed your password reset link!'
        ]);
        
        return back();
        
    } catch (\Exception $e) {
        Log::error('Failed to send password reset email', [
            'email' => $email,
            'error' => $e->getMessage(),
            'error_code' => $e->getCode(),
            'trace' => $e->getTraceAsString()
        ]);
        
        if ($request->ajax()) {
            return response()->json([
                'errors' => ['login' => ['Could not send reset link: ' . $e->getMessage()]]
            ], 422);
        }
        
        return back()->withErrors(['login' => 'Could not send reset link: ' . $e->getMessage()]);
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
            session()->flash('toastr', [
                'type' => 'error',
                'message' => 'Invalid or expired password reset token.'
            ]);
            return redirect()->route('admin.password.request');
        }
        
        // Check if token is expired (tokens valid for 60 minutes)
        $tokenCreatedAt = Carbon::parse($tokenData->created_at);
        if (Carbon::now()->diffInMinutes($tokenCreatedAt) > 60) {
            DB::table('password_reset_tokens')
                ->where('email', $email)
                ->delete();
                
            session()->flash('toastr', [
                'type' => 'error',
                'message' => 'Password reset token has expired. Please request a new link.'
            ]);
            return redirect()->route('admin.password.request');
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
            
            session()->flash('toastr', [
                'type' => 'error',
                'message' => 'Invalid or expired password reset token.'
            ]);
            return redirect()->route('admin.password.request');
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
        
        session()->flash('toastr', [
            'type' => 'success',
            'message' => 'Your password has been reset successfully!'
        ]);
        return redirect()->route('admin.dashboard');
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