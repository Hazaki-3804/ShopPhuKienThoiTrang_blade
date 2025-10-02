<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

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
        $fileName = Str::uuid() . '.' . $file->getClientOriginalExtension();
        Storage::disk('public')->putFileAs('avatars', $file, $fileName);
        return 'storage/avatars/' . $fileName;
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

        return redirect()->route('profile.index')->with('success', 'Cập nhật thông tin thành công');
    }



    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => ['required'],
            'password' => ['required', 'confirmed', 'min:8'],
        ]);

        $user = Auth::user();
        if (!Hash::check($request->input('current_password'), $user->password)) {
            return back()->with('error', 'Mật khẩu hiện tại không đúng');
        }

        $user->password = Hash::make($request->input('password'));
        $user->save();

        return redirect()->route('profile.index')->with('success', 'Đổi mật khẩu thành công');
    }
}
