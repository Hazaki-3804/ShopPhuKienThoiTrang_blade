<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SettingController extends Controller
{
    public function settings()
    {
        // Lấy tất cả settings từ database
        $settings = Setting::all()->pluck('value', 'key_name')->toArray();
        
        return view('admin.settings', compact('settings'));
    }

    public function updateSettings(Request $request)
    {
        $tab = $request->input('tab', 'general');
        
        // Validation rules theo từng tab
        $rules = [];
        switch ($tab) {
            case 'general':
                $rules = [
                    'site_name' => 'required|string|max:255',
                    'site_description' => 'nullable|string|max:1000',
                    'site_status' => 'required|in:active,maintenance',
                ];
                break;
            case 'contact':
                $rules = [
                    'contact_email' => 'required|email|max:255',
                    'contact_phone' => 'required|string|max:20',
                    'contact_address' => 'required|string|max:500',
                ];
                break;
            case 'social':
                $rules = [
                    'contact_facebook' => 'nullable|url|max:255',
                    'contact_instagram' => 'nullable|url|max:255',
                    'contact_youtube' => 'nullable|url|max:255',
                    'contact_tiktok' => 'nullable|url|max:255',
                ];
                break;
            case 'appearance':
                $rules = [
                    'site_logo' => 'nullable|image|mimes:jpeg,png,jpg,svg|max:2048',
                    'site_favicon' => 'nullable|mimes:jpeg,png,jpg,ico|max:1024',
                ];
                break;
            case 'system':
                $rules = [];
                break;
        }
        
        $request->validate($rules);

        try {
            DB::beginTransaction();

            // Xử lý upload logo
            $logoPath = $request->input('site_logo', '');
            if ($request->hasFile('logo_file')) {
                // Xóa ảnh cũ nếu có
                $oldLogo = Setting::where('key_name', 'site_logo')->value('value');
                if ($oldLogo && file_exists(public_path('img/' . $oldLogo))) {
                    @unlink(public_path('img/' . $oldLogo));
                }
                
                // Upload ảnh mới
                $file = $request->file('logo_file');
                $filename = 'logo_' . time() . '.' . $file->getClientOriginalExtension();
                $file->move(public_path('img/'), $filename);
                $logoPath = 'img/' . $filename;
            }

            // Xử lý upload favicon
            $faviconPath = $request->input('site_favicon', '');
            if ($request->hasFile('favicon_file')) {
                // Xóa ảnh cũ nếu có
                $oldFavicon = Setting::where('key_name', 'site_favicon')->value('value');
                if ($oldFavicon && file_exists(public_path($oldFavicon))) {
                    @unlink(public_path($oldFavicon));
                }
                
                // Upload ảnh mới
                $file = $request->file('favicon_file');
                $filename = 'favicon_' . time() . '.' . $file->getClientOriginalExtension();
                $file->move(public_path('img/'), $filename);
                $faviconPath = 'img/' . $filename;
            }

            // Danh sách các settings cần update theo tab
            $settingsData = [];
            
            switch ($tab) {
                case 'general':
                    $settingsData = [
                        'site_name' => $request->input('site_name'),
                        'site_description' => $request->input('site_description', ''),
                        'site_status' => $request->input('site_status'),
                    ];
                    break;
                case 'contact':
                    $settingsData = [
                        'contact_phone' => $request->input('contact_phone'),
                        'contact_email' => $request->input('contact_email'),
                        'contact_address' => $request->input('contact_address'),
                    ];
                    break;
                case 'social':
                    $settingsData = [
                        'contact_facebook' => $request->input('contact_facebook', ''),
                        'contact_instagram' => $request->input('contact_instagram', ''),
                        'contact_youtube' => $request->input('contact_youtube', ''),
                        'contact_tiktok' => $request->input('contact_tiktok', ''),
                    ];
                    break;
                case 'appearance':
                    $settingsData = [
                        'site_logo' => $logoPath,
                        'site_favicon' => $faviconPath,
                    ];
                    break;
            }

            // Update hoặc create từng setting
            foreach ($settingsData as $key => $value) {
                Setting::updateOrCreate(
                    ['key_name' => $key],
                    ['value' => $value]
                );
            }

            DB::commit();

            return redirect()->route('settings')
                ->with('success', 'Cập nhật cài đặt thành công!');

        } catch (\Exception $e) {
            DB::rollBack();
            
            return redirect()->back()
                ->withInput()
                ->withErrors(['error' => 'Có lỗi xảy ra: ' . $e->getMessage()]);
        }
    }
}
