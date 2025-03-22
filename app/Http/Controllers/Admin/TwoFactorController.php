<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\TwoFactorService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class TwoFactorController extends Controller
{
    protected $twoFactorService;

    public function __construct(TwoFactorService $twoFactorService)
    {
        $this->twoFactorService = $twoFactorService;
    }

    /**
     * Show the 2FA setup page
     */
    public function setup()
    {
        $admin = Auth::guard('admin')->user();
        
        if ($admin->two_factor_enabled) {
            return redirect()->route('admin.profile')->with('info', 'Two-factor authentication is already enabled.');
        }
        
        $secret = $this->twoFactorService->generateSecretKey();
        
        // Store the secret temporarily in the session
        session(['2fa_secret' => $secret]);
        
        $qrCodeUrl = $this->twoFactorService->getQrCodeUrl($admin);
        $qrCode = QrCode::size(200)->generate($qrCodeUrl);
        
        return view('admin.two-factor.setup', compact('secret', 'qrCode'));
    }
    
    /**
     * Enable 2FA for the admin
     */
    public function enable(Request $request)
    {
        $request->validate([
            'code' => 'required|string|size:6',
        ]);
        
        $admin = Auth::guard('admin')->user();
        $secret = session('2fa_secret');
        
        if (!$secret) {
            return redirect()->route('admin.two-factor.setup')
                ->with('error', 'The setup process has expired. Please try again.');
        }
        
        // Temporary set the secret to verify the code
        $admin->two_factor_secret = $secret;
        
        if (!$this->twoFactorService->verifyOtp($admin, $request->code)) {
            throw ValidationException::withMessages([
                'code' => ['The provided code is invalid.'],
            ]);
        }
        
        // Enable 2FA with the verified secret
        $this->twoFactorService->enableTwoFactor($admin, $secret);
        
        // Remove the temporary secret from the session
        session()->forget('2fa_secret');
        
        return redirect()->route('admin.profile')
            ->with('success', 'Two-factor authentication has been enabled successfully.');
    }
    
    /**
     * Disable 2FA for the admin
     */
    public function disable(Request $request)
    {
        $request->validate([
            'password' => 'required|current_password:admin',
        ]);
        
        $admin = Auth::guard('admin')->user();
        $this->twoFactorService->disableTwoFactor($admin);
        
        return redirect()->route('admin.profile')
            ->with('success', 'Two-factor authentication has been disabled.');
    }
    
    /**
     * Show the 2FA verification form
     */
    public function showVerificationForm()
    {
        if (!Auth::guard('admin')->check()) {
            return redirect()->route('admin.login');
        }
        
        $admin = Auth::guard('admin')->user();
        
        if (!$admin->two_factor_enabled) {
            return redirect()->route('admin.dashboard');
        }
        
        return view('admin.two-factor.verify');
    }
    
    /**
     * Verify the provided OTP
     */
    public function verify(Request $request)
    {
        $request->validate([
            'code' => 'required|string|size:6',
        ]);
        
        $admin = Auth::guard('admin')->user();
        
        if (!$admin->two_factor_enabled) {
            return redirect()->route('admin.dashboard');
        }
        
        if (!$this->twoFactorService->verifyOtp($admin, $request->code)) {
            throw ValidationException::withMessages([
                'code' => ['The provided authentication code is invalid.'],
            ]);
        }
        
        $request->session()->put('two_factor_verified', true);
        
        return redirect()->intended(route('admin.dashboard'));
    }
}