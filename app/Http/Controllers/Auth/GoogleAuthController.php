<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Laravel\Socialite\Facades\Socialite;
use Exception;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class GoogleAuthController extends Controller
{
    public function redirect()
    {
        try {
            return Socialite::driver('google')->redirect();
        } catch (Exception $e) {
            Log::error('Google OAuth redirect failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->route('login')
                ->with('error', 'Could not connect to Google. Please try again later.');
        }
    }

    public function callback()
    {
        try {
            $googleUser = Socialite::driver('google')->user();

            if (!$googleUser || !$googleUser->email) {
                Log::warning('Invalid Google user data received', [
                    'user' => $googleUser ?? null
                ]);
                return redirect()->route('login')
                    ->with('error', 'Could not get user information from Google.');
            }

            DB::beginTransaction();
            try {
                // Check if email is already in use by a non-Google account
                $existingUser = User::where('email', $googleUser->email)
                    ->whereNull('google_id')
                    ->first();

                if ($existingUser) {
                    // Update existing user with Google ID
                    $existingUser->update([
                        'google_id' => $googleUser->id,
                        'email_verified_at' => Carbon::now()
                    ]);
                    $user = $existingUser;
                } else {
                    // Create new user with verified email
                    $user = User::updateOrCreate(
                        ['email' => $googleUser->email],
                        [
                            'name' => $googleUser->name,
                            'google_id' => $googleUser->id,
                            'email_verified_at' => Carbon::now(),
                            'password' => null
                        ]
                    );
                }

                DB::commit();

                // Ensure the user instance has the email_verified_at attribute
                $user->markEmailAsVerified();
                
                Auth::login($user, true);
                
                return redirect()->intended(RouteServiceProvider::HOME);
            } catch (Exception $e) {
                DB::rollBack();
                throw $e;
            }
        } catch (Exception $e) {
            Log::error('Google OAuth callback failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->route('login')
                ->with('error', 'Could not log in with Google. Please try again later.');
        }
    }
}