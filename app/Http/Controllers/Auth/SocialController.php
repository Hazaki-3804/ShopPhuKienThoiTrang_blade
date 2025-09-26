<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

/**
 * @method static \Laravel\Socialite\Contracts\Provider stateless()
 */
class SocialController extends Controller
{
    public function redirect($provider)
    {
        return Socialite::driver($provider)->stateless()->redirect();
    }
    public function callback($provider)
    {
        try {
            $providerUser = Socialite::driver($provider)->stateless()->user();
            $user = User::where('email', $providerUser->getEmail())->first();

            if ($user) {
                // Cập nhật thông tin người dùng
                $user->update([
                    'name' => $providerUser->getName(),
                    'avatar' => $providerUser->getAvatar(),
                ]);
            } else {
                // Tạo người dùng mới với role_id = 3
                $user = User::create([
                    'username' => $providerUser->getId(),
                    'name' => $providerUser->getName(),
                    'email' => $providerUser->getEmail(),
                    'password' => Hash::make('default_password'),
                    'avatar' => $providerUser->getAvatar(),
                    'role_id' => 3,
                    'status' => 1,
                ]);
            }

            // Kiểm tra trạng thái tài khoản
            if ($user->status == 0) {
                return redirect()->route('login')
                    ->with('error', 'Tài khoản của bạn đã bị khóa. Vui lòng liên hệ quản trị viên.');
            }

            // Login người dùng
            Auth::login($user);

            // Redirect theo role
            if (in_array($user->role_id, [1, 2])) {
                return redirect()->intended(route('admin.dashboard'));
            } else { // role_id = 3
                return redirect()->intended(route('home'));
            }
        } catch (\Exception $e) {
            Log::error('Error while login with ' . $provider . ': ' . $e->getMessage());
            return redirect()->route('login')->with('status', 'Đăng nhập thất bại. Vui lòng thử lại.');
        }
    }
}
