<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Cloudinary\Cloudinary;
use Illuminate\Support\Facades\Log;

class ProfileController extends Controller
{
    public function index()
    {
        $user = Auth::user()->load(['orders' => function ($q) {
            $q->latest()->with(['order_items.product']);
        }]);

        // Optional: simple derived counters for template convenience
        $user->setAttribute('orders_count', $user->orders->count());
        $user->setAttribute('wishlist_count', $user->wishlist_count ?? 0);
        $user->setAttribute('points', $user->points ?? 0);

        return view('user.profile', compact('user'));
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
            'username' => ['sometimes', 'required', 'string', 'max:50', Rule::unique('users', 'username')->ignore($user->id)],
            'name' => ['nullable', 'string', 'max:100'],
            'email' => ['sometimes', 'required', 'email', Rule::unique('users', 'email')->ignore($user->id)],
            'phone' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string', 'max:255'],
            'avatar' => ['nullable', 'image', 'max:2048'],
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

        // Điền dữ liệu mới vào model nhưng chưa lưu
        $user->fill($validated);

        // Nếu không có bất kỳ trường nào thay đổi, cảnh báo và không lưu
        if (!$user->isDirty()) {
            return back()->with('warning', 'Chưa có thông tin thay đổi');
        }

        // Có thay đổi -> lưu
        $user->save();

        return redirect()->route('profile.index')->with('success', 'Cập nhật thông tin thành công');
    }



    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => ['required','current_password'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ],
        [
            'current_password.current_password' => 'Mật khẩu hiện tại không đúng',
            'current_password.required' => 'Vui lòng nhập mật khẩu hiện tại',
            'password.required' => 'Vui lòng nhập mật khẩu mới',
            'password.confirmed' => 'Xác nhận mật khẩu không khớp',
            'password.min' => 'Mật khẩu phải có ít nhất 8 ký tự',
            'password.regex' => 'Mật khẩu phải chứa ít nhất một chữ hoa, một chữ thường, một số và một ký tự đặc biệt',
        ]
        );

        $user = Auth::user();
        if (!Hash::check($request->input('current_password'), $user->password)) {
            return back()->with('current_password', 'Mật khẩu hiện tại không đúng');
        }

        $user->password = Hash::make($request->input('password'));
        $user->save();

        return redirect()->route('profile.index')->with('success', 'Đổi mật khẩu thành công!');
    }
}
