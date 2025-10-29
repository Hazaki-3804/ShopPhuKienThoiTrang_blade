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
use Cloudinary\Cloudinary;
use Illuminate\Support\Facades\Log;

class ProfileController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        return view('admin.profile.index', compact('user'));
    }

    public function saveAvatar($file)
    {
        try {
            // Khởi tạo Cloudinary
            $cloudinary = new Cloudinary([
                'cloud' => [
                    'cloud_name' => config('cloudinary.cloud.cloud_name'),
                    'api_key' => config('cloudinary.cloud.api_key'),
                    'api_secret' => config('cloudinary.cloud.api_secret'),
                ],
                'url' => ['secure' => true]
            ]);
            
            // Upload lên Cloudinary folder 'avatars'
            $uploadResult = $cloudinary->uploadApi()->upload(
                $file->getRealPath(),
                [
                    'folder' => 'avatars',
                    'resource_type' => 'image',
                    'transformation' => [
                        'width' => 400,
                        'height' => 400,
                        'crop' => 'fill',
                        'gravity' => 'face'
                    ]
                ]
            );
            
            return $uploadResult['secure_url'];
        } catch (\Exception $e) {
            Log::error('Avatar upload error: ' . $e->getMessage());
            throw $e;
        }
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
            // Xóa avatar cũ từ Cloudinary
            if (!empty($user->avatar) && str_contains($user->avatar, 'cloudinary.com')) {
                try {
                    $cloudinary = new Cloudinary([
                        'cloud' => [
                            'cloud_name' => config('cloudinary.cloud.cloud_name'),
                            'api_key' => config('cloudinary.cloud.api_key'),
                            'api_secret' => config('cloudinary.cloud.api_secret'),
                        ],
                        'url' => ['secure' => true]
                    ]);
                    
                    // Trích xuất public_id từ URL
                    preg_match('/\/v\d+\/(.+)\.[a-z]+$/', $user->avatar, $matches);
                    if (isset($matches[1])) {
                        $publicId = $matches[1];
                        $cloudinary->uploadApi()->destroy($publicId);
                    }
                } catch (\Exception $e) {
                    Log::warning('Failed to delete old avatar: ' . $e->getMessage());
                }
            } elseif (!empty($user->avatar) && str_starts_with($user->avatar, 'storage/')) {
                // Xóa avatar cũ từ storage local (nếu còn)
                $oldPath = str_replace('storage/', '', $user->avatar);
                if (Storage::disk('public')->exists($oldPath)) {
                    Storage::disk('public')->delete($oldPath);
                }
            }

            // Lưu avatar mới lên Cloudinary
            $validated['avatar'] = $this->saveAvatar($request->file('avatar'));
        }

        $user->fill($validated);
        $user->save();

        // Kiểm tra nếu là AJAX request
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Cập nhật thông tin thành công!',
                'type' => 'success'
            ]);
        }

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
}
