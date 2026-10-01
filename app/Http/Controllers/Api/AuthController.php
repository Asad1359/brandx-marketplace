<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Notifications\PasswordResetOtpNotification;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    /*
    |==========================================================================
    | REGISTER
    |==========================================================================
    */

    public function register(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Please correct the errors.',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $email = strtolower(trim($request->email));
            $otp = $this->generateOtp();

            $user = User::create([
                'name' => $request->name,
                'email' => $email,
                'password' => Hash::make($request->password),
                'role' => 'user',
                'is_active' => true,
                'email_verified_at' => null,
                'otp' => $otp,
                'otp_expires_at' => Carbon::now()->addMinutes(10),
            ]);

            session([
                'registration_email' => $user->email,
            ]);

            $this->sendRegistrationOtp(
                $user->name,
                $user->email,
                $otp
            );

            Auth::guard('web')->logout();

            return response()->json([
                'success' => true,
                'message' => 'Registration successful. OTP has been sent.',
                'requires_otp' => true,
                'email' => $user->email,
            ], 201);

        } catch (\Throwable $e) {
            Log::error('Registration error', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Unable to complete registration.',
            ], 500);
        }
    }


    /*
    |==========================================================================
    | VERIFY REGISTRATION OTP
    |==========================================================================
    */

    public function verifyRegistrationOtp(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'otp' => 'required|digits:6',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Please enter a valid 6-digit OTP.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $email = session('registration_email');

        if (!$email) {
            return response()->json([
                'success' => false,
                'message' => 'Registration session expired.',
            ], 419);
        }

        $user = User::where('email', $email)->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found.',
            ], 404);
        }

        if ((string) $user->otp !== (string) $request->otp) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid OTP.',
            ], 422);
        }

        if (
            !$user->otp_expires_at ||
            Carbon::now()->greaterThan(
                Carbon::parse($user->otp_expires_at)
            )
        ) {
            return response()->json([
                'success' => false,
                'message' => 'OTP has expired.',
            ], 422);
        }

        $user->update([
            'otp' => null,
            'otp_expires_at' => null,
            'email_verified_at' => Carbon::now(),
            'is_active' => true,
        ]);

        session()->forget('registration_email');

        Auth::guard('web')->login($user);

        if ($request->hasSession()) {
            $request->session()->regenerate();
        }

        return response()->json([
            'success' => true,
            'message' => 'Email verified successfully.',
            'user' => $this->userData($user),
            'redirect' => $this->dashboardRoute($user),
        ]);
    }


    /*
    |==========================================================================
    | RESEND REGISTRATION OTP
    |==========================================================================
    */

    public function resendRegistrationOtp(Request $request): JsonResponse
    {
        $email = session('registration_email')
            ?: $request->input('email');

        if (!$email) {
            return response()->json([
                'success' => false,
                'message' => 'Registration email not found.',
            ], 419);
        }

        $email = strtolower(trim($email));

        $user = User::where('email', $email)->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found.',
            ], 404);
        }

        if ($user->email_verified_at) {
            return response()->json([
                'success' => false,
                'message' => 'Email is already verified.',
            ], 422);
        }

        try {
            $otp = $this->generateOtp();

            $user->update([
                'otp' => $otp,
                'otp_expires_at' => Carbon::now()->addMinutes(10),
            ]);

            session([
                'registration_email' => $user->email,
            ]);

            $this->sendRegistrationOtp(
                $user->name,
                $user->email,
                $otp
            );

            return response()->json([
                'success' => true,
                'message' => 'New OTP has been sent.',
                'email' => $user->email,
            ]);

        } catch (\Throwable $e) {
            Log::error('Resend registration OTP error', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Unable to send OTP.',
            ], 500);
        }
    }


    /*
    |==========================================================================
    | LOGIN
    |==========================================================================
    */

    public function login(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Please enter email and password.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $email = strtolower(trim($request->email));

        $user = User::where('email', $email)->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'No account found with this email address.',
                'reason' => 'user_not_found',
            ], 401);
        }

        if (!Hash::check($request->password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Incorrect password.',
                'reason' => 'invalid_password',
            ], 401);
        }

        if (!(bool) $user->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Your account has been deactivated.',
                'reason' => 'account_inactive',
            ], 403);
        }

        /*
        |----------------------------------------------------------------------
        | EMAIL NOT VERIFIED
        |----------------------------------------------------------------------
        */

        if (!$user->email_verified_at) {
            $otp = $this->generateOtp();

            $user->update([
                'otp' => $otp,
                'otp_expires_at' => Carbon::now()->addMinutes(10),
            ]);

            session([
                'registration_email' => $user->email,
            ]);

            try {
                $this->sendRegistrationOtp(
                    $user->name,
                    $user->email,
                    $otp
                );

            } catch (\Throwable $e) {
                Log::error('Login OTP email error', [
                    'email' => $user->email,
                    'error' => $e->getMessage(),
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Unable to send verification OTP.',
                    'reason' => 'otp_email_failed',
                ], 500);
            }

            Auth::guard('web')->logout();

            return response()->json([
                'success' => true,
                'requires_otp' => true,
                'message' => 'Email is not verified. A new OTP has been sent.',
                'email' => $user->email,
            ]);
        }

        /*
        |----------------------------------------------------------------------
        | LOGIN
        |----------------------------------------------------------------------
        */

        Auth::guard('web')->login($user);

        if ($request->hasSession()) {
            $request->session()->regenerate();
        }

        /*
        |----------------------------------------------------------------------
        | DASHBOARD
        |----------------------------------------------------------------------
        */

        $redirect = $this->dashboardRoute($user);

        return response()->json([
            'success' => true,
            'message' => 'Login successful.',
            'user' => $this->userData($user),
            'redirect' => $redirect,
        ]);
    }


    /*
    |==========================================================================
    | CURRENT USER
    |==========================================================================
    */

    public function user(): JsonResponse
    {
        $user = Auth::guard('web')->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated.',
            ], 401);
        }

        return response()->json([
            'success' => true,
            'user' => $this->userData($user),
            'redirect' => $this->dashboardRoute($user),
        ]);
    }


    /*
    |==========================================================================
    | LOGOUT
    |==========================================================================
    | Fixed version — handles session-safe logout with try/catch.
    */

    public function logout(Request $request): JsonResponse
    {
        try {

            /*
            |------------------------------------------------------------------
            | LOGOUT FROM WEB GUARD
            |------------------------------------------------------------------
            */

            Auth::guard('web')->logout();


            /*
            |------------------------------------------------------------------
            | INVALIDATE SESSION (only if session exists)
            |------------------------------------------------------------------
            */

            if ($request->hasSession()) {

                $request->session()->invalidate();
                $request->session()->regenerateToken();
            }


            /*
            |------------------------------------------------------------------
            | SUCCESS
            |------------------------------------------------------------------
            */

            return response()->json([
                'success' => true,
                'message' => 'Logged out successfully.',
            ]);

        } catch (\Throwable $e) {

            /*
            |------------------------------------------------------------------
            | LOG FULL ERROR
            |------------------------------------------------------------------
            */

            Log::error('Logout error', [
                'message' => $e->getMessage(),
                'file'    => $e->getFile(),
                'line'    => $e->getLine(),
            ]);


            /*
            |------------------------------------------------------------------
            | RETURN ERROR DETAILS
            |------------------------------------------------------------------
            */

            return response()->json([
                'success' => false,
                'message' => 'Logout failed: ' . $e->getMessage(),
                'file'    => $e->getFile(),
                'line'    => $e->getLine(),
            ], 500);
        }
    }


    /*
    |==========================================================================
    | FORGOT PASSWORD
    |==========================================================================
    */

    public function forgotPassword(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Please enter a valid email address.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $email = strtolower(trim($request->email));

        $user = User::where('email', $email)->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'No account found with this email.',
            ], 404);
        }

        if (!(bool) $user->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Your account has been deactivated.',
            ], 403);
        }

        try {
            $otp = $this->generateOtp();

            $user->update([
                'otp' => $otp,
                'otp_expires_at' => Carbon::now()->addMinutes(10),
            ]);

            session([
                'password_reset_email' => $user->email,
                'password_reset_verified' => false,
            ]);

            $user->notify(
                new PasswordResetOtpNotification($otp)
            );

            return response()->json([
                'success' => true,
                'message' => 'Password reset OTP has been sent.',
                'email' => $user->email,
            ]);

        } catch (\Throwable $e) {
            Log::error('Forgot password OTP error', [
                'email' => $email,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Unable to send OTP. Please check your email configuration.',
            ], 500);
        }
    }


    /*
    |==========================================================================
    | VERIFY PASSWORD RESET OTP
    |==========================================================================
    */

    public function verifyPasswordResetOtp(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'otp' => 'required|digits:6',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Please enter a valid 6-digit OTP.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $email = session('password_reset_email');

        if (!$email) {
            return response()->json([
                'success' => false,
                'message' => 'Password reset session expired. Please request a new OTP.',
            ], 419);
        }

        $user = User::where('email', $email)->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found.',
            ], 404);
        }

        if (
            !$user->otp ||
            (string) $user->otp !== (string) $request->otp
        ) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid OTP.',
            ], 422);
        }

        if (
            !$user->otp_expires_at ||
            Carbon::now()->greaterThan(
                Carbon::parse($user->otp_expires_at)
            )
        ) {
            return response()->json([
                'success' => false,
                'message' => 'OTP has expired. Please request a new OTP.',
            ], 422);
        }

        $user->update([
            'otp' => null,
            'otp_expires_at' => null,
        ]);

        session([
            'password_reset_verified' => true,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'OTP verified successfully.',
            'email' => $user->email,
            'redirect' => '/reset-password',
        ]);
    }


    /*
    |==========================================================================
    | RESEND PASSWORD RESET OTP
    |==========================================================================
    */

    public function resendPasswordResetOtp(Request $request): JsonResponse
    {
        $email = session('password_reset_email')
            ?: $request->input('email');

        if (!$email) {
            return response()->json([
                'success' => false,
                'message' => 'Email not found. Please start again.',
            ], 419);
        }

        $email = strtolower(trim($email));

        $user = User::where('email', $email)->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found.',
            ], 404);
        }

        if (!(bool) $user->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Your account has been deactivated.',
            ], 403);
        }

        try {
            $otp = $this->generateOtp();

            $user->update([
                'otp' => $otp,
                'otp_expires_at' => Carbon::now()->addMinutes(10),
            ]);

            session([
                'password_reset_email' => $user->email,
                'password_reset_verified' => false,
            ]);

            $user->notify(
                new PasswordResetOtpNotification($otp)
            );

            return response()->json([
                'success' => true,
                'message' => 'New password reset OTP has been sent.',
                'email' => $user->email,
            ]);

        } catch (\Throwable $e) {
            Log::error('Resend password OTP error', [
                'email' => $email,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Unable to send OTP. Please check your email configuration.',
            ], 500);
        }
    }


    /*
    |==========================================================================
    | RESET PASSWORD
    |==========================================================================
    */

    public function resetPassword(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Password must be at least 8 characters and confirmation must match.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $email = session('password_reset_email');
        $verified = session('password_reset_verified', false);

        if (!$email || !$verified) {
            return response()->json([
                'success' => false,
                'message' => 'Password reset authorization expired. Please request a new OTP.',
            ], 419);
        }

        $user = User::where('email', $email)->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found.',
            ], 404);
        }

        $user->update([
            'password' => Hash::make($request->password),
            'otp' => null,
            'otp_expires_at' => null,
        ]);

        session()->forget([
            'password_reset_email',
            'password_reset_verified',
        ]);

        Auth::guard('web')->logout();

        return response()->json([
            'success' => true,
            'message' => 'Password reset successfully. You can now login.',
            'redirect' => '/login',
        ]);
    }


    /*
    |==========================================================================
    | GENERATE OTP
    |==========================================================================
    */

    private function generateOtp(): string
    {
        return (string) random_int(100000, 999999);
    }


    /*
    |==========================================================================
    | SEND REGISTRATION OTP
    |==========================================================================
    */

    private function sendRegistrationOtp(
        string $name,
        string $email,
        string $otp
    ): void {
        Mail::send(
            'emails.registration-otp',
            [
                'name' => $name,
                'otp' => $otp,
            ],
            function ($message) use ($email) {
                $message
                    ->to($email)
                    ->subject('BrandX Email Verification OTP');
            }
        );
    }


    /*
    |==========================================================================
    | USER DATA
    |==========================================================================
    */

    private function userData(User $user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role,
            'is_active' => (bool) $user->is_active,
            'email_verified_at' => $user->email_verified_at,
            'profile_image' => $user->profile_image ?? null,
        ];
    }


    /*
    |==========================================================================
    | DASHBOARD ROUTE
    |==========================================================================
    */

    private function dashboardRoute(User $user): string
    {
        if ($user->role === 'admin') {
            return '/admin/dashboard';
        }

        return '/dashboard';
    }
}