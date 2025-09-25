<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function loginForm() { return view('auth.login'); }

    public function registerForm() { return view('auth.register'); }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required','email'],
            'password' => ['required','string']
        ]);
        $remember = (bool)$request->boolean('remember');
        if (Auth::attempt($credentials, $remember)) {
            $request->session()->regenerate();
            if (Auth::user()->status !== 'active') {
                Auth::logout();
                return back()->withErrors(['email'=>'Tài khoản không hoạt động']);
            }
            return $this->redirectByRole();
        }
        return back()->withErrors(['email' => 'Thông tin đăng nhập không đúng']);
    }

    public function register(Request $request)
    {
        $data = $request->validate([
            'name' => ['required','string','max:120'],
            'email' => ['required','email','unique:users,email'],
            'password' => ['required','string','min:6','confirmed'],
        ]);
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role_id' => 2,
        ]);
        Auth::login($user);
        return $this->redirectByRole();
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('home');
    }

    private function redirectByRole()
    {
        $role = Auth::user()->role_id ?? 2;
        return redirect()->route('home');
    }

    // Forgot password (custom token)
    public function forgotForm() { return view('auth.forgot'); }

    public function forgotSend(Request $request)
    {
        $data = $request->validate(['email'=>['required','email']]);
        $user = User::where('email', $data['email'])->first();
        if ($user) {
            $user->reset_token = Str::random(40);
            $user->reset_token_expires_at = now()->addHour();
            $user->save();
            // TODO: Send email in real app
        }
        return back()->with('status', 'Nếu email tồn tại, liên kết đặt lại đã được gửi.');
    }

    public function resetForm(string $token)
    {
        return view('auth.reset', compact('token'));
    }

    public function resetPassword(Request $request)
    {
        $data = $request->validate([
            'token' => ['required','string'],
            'password' => ['required','string','min:6','confirmed'],
        ]);
        $user = User::where('reset_token', $data['token'])
            ->where('reset_token_expires_at', '>=', now())
            ->first();
        if (!$user) return back()->withErrors(['token'=>'Token không hợp lệ hoặc đã hết hạn']);
        $user->password = Hash::make($data['password']);
        $user->reset_token = null;
        $user->reset_token_expires_at = null;
        $user->save();
        return redirect()->route('login')->with('status', 'Đã cập nhật mật khẩu.');
    }

    public function changePasswordForm() { return view('auth.change'); }

    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => ['required','string'],
            'password' => ['required','string','min:6','confirmed'],
        ]);
        $user = $request->user();
        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password'=>'Mật khẩu hiện tại không đúng']);
        }
        $user->password = Hash::make($request->password);
        $user->save();
        return back()->with('status', 'Đã đổi mật khẩu');
    }
}


