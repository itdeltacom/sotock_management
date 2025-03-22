<?php

namespace App\Services;

use App\Models\Admin;
use PragmaRX\Google2FA\Google2FA;
use Illuminate\Support\Facades\Hash;

class TwoFactorService
{
    protected $google2fa;

    public function __construct()
    {
        $this->google2fa = new Google2FA();
    }

    /**
     * Generate a new secret key for the admin
     * 
     * Google Authenticator requires proper Base32 encoding with 
     * specific character sets and no padding
     */
    public function generateSecretKey(): string
    {
        // Generate a standard 16-character secret (80 bits) which is Google Authenticator's preferred length
        return trim($this->google2fa->generateSecretKey(16));
    }

    /**
     * Get the QR code URL for the admin
     * 
     * Google Authenticator is very strict about the format:
     * - Must be otpauth://totp/
     * - Secret must be properly Base32 encoded
     * - Label format should be "Issuer:account" 
     */
    public function getQrCodeUrl(Admin $admin): string
    {
        // Get the secret from session
        $secret = session('2fa_secret');
        
        // Format issuer and account name properly for Google Authenticator
        $issuer = config('app.name');
        $accountName = $admin->email;
        
        // Create the URL manually to ensure proper formatting
        $url = sprintf(
            'otpauth://totp/%s:%s?secret=%s&issuer=%s&algorithm=SHA1&digits=6&period=30',
            rawurlencode($issuer),
            rawurlencode($accountName),
            $secret,
            rawurlencode($issuer)
        );
        
        return $url;
    }

    /**
     * Enable 2FA for the admin
     */
    public function enableTwoFactor(Admin $admin, string $secret): void
    {
        $admin->update([
            'two_factor_secret' => $secret,
            'two_factor_enabled' => true,
        ]);
    }

    /**
     * Disable 2FA for the admin
     */
    public function disableTwoFactor(Admin $admin): void
    {
        $admin->update([
            'two_factor_secret' => null,
            'two_factor_enabled' => false,
        ]);
    }

    /**
     * Verify the provided OTP
     */
    public function verifyOtp(Admin $admin, string $otp): bool
    {
        // Get the secret - either from the admin model or from the session
        $secret = $admin->two_factor_secret ?? session('2fa_secret');
        
        if (empty($secret)) {
            return false;
        }
        
        // Allow for some time drift (window = 1 means ±30 seconds)
        return $this->google2fa->verifyKey($secret, $otp, 1);
    }
}