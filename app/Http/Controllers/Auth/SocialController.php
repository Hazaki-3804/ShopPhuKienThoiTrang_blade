<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

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
                $avatar = $user->avatar;
                if (empty($avatar)) {
                    $avatar = $this->saveAvatar($providerUser);
                }
                $user->update([
                    'name' => $providerUser->getName(),
                    'avatar' => $avatar,
                    'social_id' => 1,
                ]);
            } else {
                // Tạo người dùng mới với role_id = 3
                $user = User::create([
                    'username' => $providerUser->getId(),
                    'name' => $providerUser->getName(),
                    'email' => $providerUser->getEmail(),
                    'password' => Hash::make('default_password'),
                    'avatar' => $this->saveAvatar($providerUser),
                    'role_id' => 3,
                    'status' => 1,
                    'social_id' => 1,
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
                return redirect()->intended(route('dashboard'));
            } else { // role_id = 3
                return redirect()->intended(route('home'));
            }
        } catch (\Exception $e) {
            Log::error('Error while login with ' . $provider . ': ' . $e->getMessage());
            return redirect()->route('login')->with('status', 'Đăng nhập thất bại. Vui lòng thử lại.');
        }
    }
    public function saveAvatar($providerUser)
    {
        $avatar = $providerUser->getAvatar();
        $avatarName = $providerUser->getId() . '.png';
        Storage::disk('public')->put('avatars/' . $avatarName, file_get_contents($avatar));
        return 'storage/avatars/' . $avatarName;
    }
}
