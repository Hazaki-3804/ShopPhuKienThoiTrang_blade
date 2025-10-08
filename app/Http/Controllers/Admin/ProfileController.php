<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        return view('admin.profile.index', compact('user'));
    }

    public function saveAvatar($file)
    {
        $fileName = Str::uuid() . '.' . $file->getClientOriginalExtension();
        Storage::disk('public')->putFileAs('avatars', $file, $fileName);
        return 'storage/avatars/' . $fileName;
    }

    public function update(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', Rule::unique('users', 'email')->ignore($user->id)],
            'phone' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string', 'max:255'],
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        if ($request->hasFile('avatar')) {
            // Xóa avatar cũ
            if (!empty($user->avatar) && str_starts_with($user->avatar, 'storage/')) {
                $oldPath = str_replace('storage/', '', $user->avatar);
                if (Storage::disk('public')->exists($oldPath)) {
                    Storage::disk('public')->delete($oldPath);
                }
            }

            // Lưu avatar mới
            $validated['avatar'] = $this->saveAvatar($request->file('avatar'));
        }

        $user->fill($validated);
        $user->save();

        return redirect()->route('admin.profile.index')->with('success', 'Cập nhật thông tin thành công');
    }

    public function changePasswordForm()
    {
        return view('admin.profile.change-password');
    }

    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'password' => ['required', 'confirmed', Password::min(8)],
        ]);

        $user = Auth::user();

        // Kiểm tra mật khẩu hiện tại
        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'Mật khẩu hiện tại không đúng!']);
        }

        // Cập nhật mật khẩu mới
        $user->update([
            'password' => Hash::make($request->password)
        ]);

        // Đăng xuất và chuyển về trang đăng nhập
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    public function settings()
    {
        $settings = [
            'site_name' => config('app.name', 'Shop Nàng Thơ'),
            'site_description' => 'Phụ kiện thời trang cao cấp',
            'contact_email' => 'info@shopnangTho.com',
            'contact_phone' => '0123.456.789',
            'contact_address' => '123 Đường ABC, Quận XYZ, TP.HCM',
            'maintenance_mode' => false,
            'allow_registration' => true,
        ];
        
        return view('admin.profile.settings', compact('settings'));
    }

    public function updateSettings(Request $request)
    {
        $request->validate([
            'site_name' => 'required|string|max:255',
            'site_description' => 'nullable|string|max:500',
            'contact_email' => 'required|email',
            'contact_phone' => 'nullable|string|max:20',
            'contact_address' => 'nullable|string|max:255',
            'maintenance_mode' => 'boolean',
            'allow_registration' => 'boolean',
        ]);

        // Lưu settings vào cache hoặc config
        // Đây chỉ là demo, trong thực tế bạn có thể lưu vào database
        
        return redirect()->route('admin.profile.settings')->with('success', 'Cập nhật cài đặt thành công!');
    }
}
