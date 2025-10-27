<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Mail;
use App\Mail\ResetPasswordMail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    public function loginForm()
    {
        return view('auth.login');
    }

    public function registerForm()
    {
        return view('auth.register');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string']
        ]);
        if (!$this->verifyCloudflareCaptcha($request->input('cf-turnstile-response'))) {
            return back()->withErrors(['captcha' => 'Xác minh không hợp lệ. Vui lòng thử lại.'])->withInput();;
        }
        // Keys theo email + IP
        $baseKey = 'login:'.strtolower($request->input('email')).'|'.$request->ip();
        $stageKey = $baseKey.':stage';          // 0 = giai đoạn đầu (5 lần/1 phút)
        $lockKey  = $baseKey.':lock';           // lưu timestamp hết hạn khoá (epoch seconds)
        $a0Key    = $baseKey.':attempts0';      // đếm sai cho stage 0
        $aNKey    = $baseKey.':attemptsN';      // đếm sai liên tiếp cho stage >= 1

        // Nếu đang bị khoá thì chặn
        $lockUntil = Cache::get($lockKey);
        if ($lockUntil && $lockUntil > time()) {
            $seconds = $lockUntil - time();
            return back()
                ->withErrors(['email' => "Bạn đã nhập sai quá nhiều lần. Vui lòng thử lại sau {$seconds} giây."])
                ->with('lockUntil', $lockUntil)
                ->withInput();
        }

        $remember = (bool)$request->boolean('remember');
        if (Auth::attempt($credentials, $remember)) {
            // Đăng nhập thành công -> reset mọi thứ
            Cache::forget($a0Key);
            Cache::forget($aNKey);
            Cache::forget($stageKey);
            Cache::forget($lockKey);
            if(Auth::user()->status !== 1) {
                Auth::logout();
                return back()->withErrors(['login_error' => 'Tài khoản này không hoạt động. Vui lòng liên hệ quản trị viên.'])->withInput();
            }
            if(Auth::user()->email_verified_at === null) {
                Auth::logout();
                return back()->withErrors(['email_verified_error' => 'Tài khoản này chưa được xác nhận. Vui lòng xác nhận email.', 'email_verified_url' => route('verify.email.form', ['email' => $request->input('email')])])->withInput();
            }
            $request->session()->regenerate();
            $redirect = $this->consumePendingAddToCart($request);
            if ($redirect) return $redirect;
            return $this->redirectByRole();
        }

        // Đăng nhập thất bại -> xử lý theo stage
        $stage = (int) (Cache::get($stageKey) ?? 0);

        if ($stage === 0) {
            // Giai đoạn 0: 5 lần sai -> khoá 1 phút, sau đó chuyển sang stage 1
            $attempts = (int) Cache::increment($a0Key);
            // đảm bảo attempts key có TTL hợp lý (đặt 15 phút để tự xoá nếu bỏ dở)
            Cache::put($a0Key, $attempts, now()->addMinutes(15));

            if ($attempts >= 5) {
                $duration = 60; // giây
                $until = time() + $duration;
                Cache::put($lockKey, $until, now()->addSeconds($duration));
                Cache::forget($a0Key);
                // chuyển sang stage 1 sau khi hết 1 phút đầu
                Cache::put($stageKey, 1, now()->addDays(1));
                return back()
                    ->withErrors(['email' => "Bạn đã nhập sai quá nhiều lần. Vui lòng thử lại sau {$duration} giây."])
                    ->with('lockUntil', $until)
                    ->withInput();
            }
        } else {
            // Giai đoạn >= 1: cứ 3 lần sai liên tiếp -> khoá (stage * 5 phút), rồi tăng stage
            $attemptsN = (int) Cache::increment($aNKey);
            Cache::put($aNKey, $attemptsN, now()->addMinutes(30));

            if ($attemptsN >= 3) {
                $nextStage = $stage + 1; // tăng bậc cho lần khoá tiếp theo
                $minutes = $stage * 5;   // stage=1 => 5', stage=2 => 10', ...
                if ($minutes < 5) { $minutes = 5; }
                $duration = $minutes * 60; // giây
                $until = time() + $duration;
                Cache::put($lockKey, $until, now()->addSeconds($duration));
                Cache::forget($aNKey);
                Cache::put($stageKey, $nextStage, now()->addDays(1));
                return back()
                    ->withErrors(['email' => "Bạn đã nhập sai quá nhiều lần. Vui lòng thử lại sau {$minutes} phút."])
                    ->with('lockUntil', $until)
                    ->withInput();
            }
        }

        return back()->withErrors(['email' => 'Email hoặc mật khẩu không đúng'])->withInput();
    }

    public function register(Request $request)
    {
        $data = $request->validate([
            'username' => ['nullable', 'string', 'max:50'],
            'name' => ['required', 'string', 'max:120', 'regex:/^[\pL\s]+$/u'],
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => [
                'required',
                'string',
                'min:8',
                'confirmed',
                // at least 1 lowercase, 1 uppercase, 1 digit, 1 special character
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^A-Za-z0-9]).{8,}$/'
            ],
            'password_confirmation' => ['required'],
            'phone' => ['required', 'string', 'max:15', 'regex:/^0\d{9,10}$/', 'unique:users,phone'],
            'address' => ['required', 'string', 'max:255'],
            'province' => ['required', 'integer'],
            'ward' => ['required', 'integer'],
        ], [
            'password.min' => 'Mật khẩu phải có tối thiểu 8 ký tự.',
            'password.confirmed' => 'Xác nhận mật khẩu không khớp.',
            'password.regex' => 'Mật khẩu phải có ít nhất 1 chữ hoa, 1 chữ thường, 1 số và 1 ký tự đặc biệt.'
        ]);
        if (!$this->verifyCloudflareCaptcha($request->input('cf-turnstile-response'))) {
            Log::error('Cloudflare Captcha verification failed', $this->verifyCloudflareCaptcha($request->input('cf-turnstile-response'))?['success' => true]:['success' => false]);
            return back()->withErrors(['captcha' => 'Xác minh không hợp lệ. Vui lòng thử lại.'])->withInput();
        }
        if (empty($data['username'])) {
            $data['username'] = Str::slug($data['name'], '');
        }
        $user = User::create([
            'username' => $data['username'] ?? null,
            'phone' => $data['phone'],
            'address' => $data['address'],
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'ward_id' => $data['ward'],
            'role_id' => 3,
            'status' => 1,
            'avatar' => 'https://ui-avatars.com/api/?name=' . urlencode($data['name']) . '&background=random&color=fff&size=40',
        ]);
        $user->assignRole('Khách hàng');
        // Generate 6-digit email verification code, store for 60s and send via email
        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $cacheKey = 'email_verify_code:'.strtolower($user->email);
        Cache::put($cacheKey, $code, now()->addSeconds(60));
        // also store an absolute expiry and reset resend counter
        $expiresKey = 'email_verify_expires:'.strtolower($user->email);
        $expiresAt = time() + 60;
        Cache::put($expiresKey, $expiresAt, now()->addSeconds(60));
        Cache::put('email_verify_resend_count:'.strtolower($user->email), 0, now()->addMinutes(10));

        try {
            Mail::raw("Mã xác nhận email của bạn là: {$code}. Mã có hiệu lực trong 60 giây.", function ($message) use ($user) {
                $message->to($user->email)
                        ->subject('Xác nhận email - Nàng Thơ Shop');
            });
        } catch (\Throwable $e) {
            // If email fails, still allow user to retry by going to verify page
        }

        return redirect()->route('verify.email.form', ['email' => $user->email])
            ->with('verify_expires_at', $expiresAt);
    }

    public function verifyEmailForm(Request $request)
    {
        $email = $request->query('email');
        if (!$email) {
            return redirect()->route('register')->withErrors(['email' => 'Thiếu email để xác nhận.']);
        }
        $expiresKey = 'email_verify_expires:'.strtolower($email);
        $cachedExpires = Cache::get($expiresKey);
        // If not found, default to past time so UI shows expired
        $expiresAt = is_numeric($cachedExpires) ? (int)$cachedExpires : (time() - 1);
        return view('auth.verify-email', compact('email', 'expiresAt'));
    }

    public function verifyEmailSubmit(Request $request)
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
            'code' => ['required', 'digits:6'],
        ],[
            'code.digits'=>'Mã xác nhận phải có 6 chữ số.'
        ]);

        $cacheKey = 'email_verify_code:'.strtolower($data['email']);
        $stored = Cache::get($cacheKey);
        if (!$stored) {
            return redirect()->route('verify.email.form', ['email' => $data['email']])
                ->withErrors(['code' => 'Mã đã hết hạn. Vui lòng đăng ký lại hoặc yêu cầu gửi lại mã.'])
                ->withInput();
        }
        if ($stored !== $data['code']) {
            return redirect()->route('verify.email.form', ['email' => $data['email']])
                ->withErrors(['code' => 'Mã xác nhận không đúng.'])
                ->withInput();
        }
        $user = User::where('email', $data['email'])->first();
        $user->email_verified_at = now();
        $user->save();

        // Valid code -> delete caches and redirect to login
        Cache::forget($cacheKey);
        Cache::forget('email_verify_expires:'.strtolower($data['email']));
        Cache::forget('email_verify_resend_count:'.strtolower($data['email']));
        return redirect()->route('login')->with('status', 'Xác nhận email thành công. Vui lòng đăng nhập.');
    }

    public function resendVerifyEmail(Request $request)
    {
        $data = $request->validate([
            'email' => ['required', 'email']
        ]);
        $email = strtolower($data['email']);
        $user = User::where('email', $email)->first();
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Không tìm thấy người dùng.'], 404);
        }
        // Check resend count
        $countKey = 'email_verify_resend_count:'.$email;
        $current = (int) (Cache::get($countKey) ?? 0);
        if ($current >= 3) {
            // cancel registration: delete user and clear caches
            Cache::forget('email_verify_code:'.$email);
            Cache::forget('email_verify_expires:'.$email);
            Cache::forget($countKey);
            try { $user->delete(); } catch (\Throwable $e) {}
            return response()->json(['success' => false, 'message' => 'Bạn đã vượt quá số lần gửi lại mã (3 lần). Đăng ký đã bị hủy.', 'canceled' => true], 400);
        }
        Cache::put($countKey, $current + 1, now()->addMinutes(10));
        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $cacheKey = 'email_verify_code:'.$email;
        Cache::put($cacheKey, $code, now()->addSeconds(60));
        $expiresKey = 'email_verify_expires:'.$email;
        $expiresAt = time() + 60;
        Cache::put($expiresKey, $expiresAt, now()->addSeconds(60));
        try {
            Mail::raw("Mã xác nhận email của bạn là: {$code}. Mã có hiệu lực trong 60 giây.", function ($message) use ($user) {
                $message->to($user->email)->subject('Xác nhận email - Nàng Thơ Shop');
            });
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => 'Gửi email thất bại.'], 500);
        }
        return response()->json(['success' => true, 'expires_at' => $expiresAt, 'resend_count' => $current + 1]);
    }
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('home');
    }
    protected function redirectByRole()
    {
        try{
        if (Auth::user()->role_id === 1) {
            return redirect()->intended(route('dashboard'));
        }
        return redirect()->intended(route('home'));
    }catch(Exception $e){
        \Log::error($e);

    }
    }
    private function consumePendingAddToCart(Request $request)
    {
        $pending = $request->session()->pull('pending_add_to_cart');
        if (!$pending || !Auth::check()) return null;
        $productId = (int)($pending['product_id'] ?? 0);
        $qty = max(1, (int)($pending['qty'] ?? 1));
        $intended = $pending['intended'] ?? null;

        $product = Product::where('status', 1)->find($productId);
        if (!$product) return $intended ? redirect()->to($intended) : null;
        if ($product->stock < $qty) $qty = $product->stock;
        if ($qty < 1) return $intended ? redirect()->to($intended) : null;

        $cart = Cart::firstOrCreate(['user_id' => Auth::id()], ['user_id' => Auth::id()]);
        $item = CartItem::firstOrNew(['cart_id' => $cart->id, 'product_id' => $product->id]);
        $item->quantity = (int)$item->quantity + $qty;
        // Do not exceed stock
        if ($item->quantity > $product->stock) $item->quantity = (int)$product->stock;
        $item->save();

        return $intended ? redirect()->to($intended) : null;
    }

    // Forgot password (custom token)
    public function forgotForm()
    {
        return view('auth.forgot');
    }

    public function forgotSend(Request $request)
    {
        $data = $request->validate(['email' => ['required', 'email']]);
        $user = User::where('email', $data['email'])->first();
        if (!$this->verifyCloudflareCaptcha($request->input('cf-turnstile-response'))) {
            return back()->withErrors(['captcha' => 'Xác minh không hợp lệ. Vui lòng thử lại.']);
        }
        if ($user) {
            // Xóa token cũ nếu có
            DB::table('password_reset_tokens')->where('email', $user->email)->delete();
            
            // Tạo token mới
            $token = Str::random(60);
            DB::table('password_reset_tokens')->insert([
                'email' => $user->email,
                'token' => Hash::make($token),
                'created_at' => now()
            ]);
            
            // Build reset URL and send email
            $resetUrl = route('password.reset', ['token' => $token]) . '?email=' . urlencode($user->email);
            Mail::to($user->email)->send(new ResetPasswordMail($resetUrl));
            
            // In production, do not expose reset link via session
        }
        return back()->with('status', 'Hệ thống đã gửi liên kết khôi phục mật khẩu đến email đã đăng ký.');
    }

    public function resetForm(string $token)
    {
        return view('auth.reset', compact('token'));
    }

    public function resetPassword(Request $request)
    {
        $data = $request->validate([
            'token' => ['required', 'string'],
            'password' => [
                'required',
                'string',
                'min:8',
                'confirmed',
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^A-Za-z0-9]).{8,}$/'
            ],
            'email' => ['required', 'email']
        ], [
            'password.min' => 'Mật khẩu phải có tối thiểu 8 ký tự.',
            'password.confirmed' => 'Xác nhận mật khẩu không khớp.',
            'password.regex' => 'Mật khẩu phải có ít nhất 1 chữ hoa, 1 chữ thường, 1 số và 1 ký tự đặc biệt.'
        ]);
        
        // Kiểm tra token trong bảng password_reset_tokens
        $resetRecord = DB::table('password_reset_tokens')
            ->where('email', $data['email'])
            ->first();
            
        if (!$resetRecord || !Hash::check($data['token'], $resetRecord->token)) {
            return back()->withErrors(['token' => 'Token không hợp lệ hoặc đã hết hạn']);
        }
        
        // Kiểm tra thời gian hết hạn (60 phút)
        if (now()->diffInMinutes($resetRecord->created_at) > 60) {
            DB::table('password_reset_tokens')->where('email', $data['email'])->delete();
            return back()->withErrors(['token' => 'Token đã hết hạn']);
        }
        
        // Cập nhật mật khẩu
        $user = User::where('email', $data['email'])->first();
        if (!$user) {
            return back()->withErrors(['email' => 'Không tìm thấy người dùng']);
        }
        
        $user->password = Hash::make($data['password']);
        $user->save();
        
        // Xóa token sau khi sử dụng
        DB::table('password_reset_tokens')->where('email', $data['email'])->delete();
        
        return redirect()->route('login')->with('status', 'Đã cập nhật mật khẩu.');
    }

    public function changePasswordForm()
    {
        return view('auth.change');
    }

    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => ['required', 'string'],
            'password' => [
                'required',
                'string',
                'min:8',
                'confirmed',
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^A-Za-z0-9]).{8,}$/'
            ],
        ], [
            'password.min' => 'Mật khẩu phải có tối thiểu 8 ký tự.',
            'password.confirmed' => 'Xác nhận mật khẩu không khớp.',
            'password.regex' => 'Mật khẩu phải có ít nhất 1 chữ hoa, 1 chữ thường, 1 số và 1 ký tự đặc biệt.'
        ]);
        $user = $request->user();
        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'Mật khẩu hiện tại không đúng']);
        }
        $user->password = Hash::make($request->password);
        $user->save();
        return back()->with('status', 'Đã đổi mật khẩu');
    }
     private function verifyCloudflareCaptcha($token)
    {
        $secret_key=config('services.cloudflare-turnslite.secret_key');
        $response = Http::asForm()->post('https://challenges.cloudflare.com/turnstile/v0/siteverify', [
            'secret' => $secret_key,
            'response' => $token,
        ]);
        if (!$response->json('success')) {
            Log::error('Cloudflare Captcha verification failed', ['response'=>$response->json()]);
            return false;
        }
        return true;
    }
}
