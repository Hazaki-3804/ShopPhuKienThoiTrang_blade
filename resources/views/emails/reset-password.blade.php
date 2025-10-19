@php($appName = 'Shop Nàng thơ')
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đặt lại mật khẩu - {{ $appName }}</title>
</head>

<body style="font-family:Arial, Helvetica, sans-serif;background:#f4f6f8;padding:20px;margin:0;">
    <table width="100%" cellspacing="0" cellpadding="0">
        <tr>
            <td align="center">
                <table width="560" cellspacing="0" cellpadding="0" style="background:#ffffff;border-radius:10px;overflow:hidden;border:1px solid #e5e5e5;box-shadow:0 2px 8px rgba(0,0,0,0.05);">

                    <!-- Header -->
                    <tr>
                        <td align="center" style="padding:10px;">
                            <img src="{{ $logo}}" alt="Logo" style="width: 50px; border-radius: 50%;">
                            <h1 style="margin:0;color:#000;font-size:20px;">{{ $appName }}</h1>
                        </td>
                    </tr>

                    <!-- Nội dung -->
                    <tr>
                        <td style="padding:30px 24px 10px 24px;color:#333;">
                            <h2 style="margin:0 0 10px 0;color:#222;font-size:18px;">Yêu cầu đặt lại mật khẩu</h2>
                            <p style="margin:0;color:#555;line-height:1.5;">
                                Chúng tôi nhận được yêu cầu đặt lại mật khẩu cho tài khoản của bạn.
                                Vui lòng nhấn nút bên dưới để đặt lại mật khẩu.
                            </p>
                        </td>
                    </tr>

                    <!-- Nút CTA -->
                    <tr>
                        <td align="center" style="padding:20px;">
                            <a href="{{ $resetUrl }}"
                                style="display:inline-block;background:#0d6efd;color:#fff;text-decoration:none;
                                      padding:12px 28px;border-radius:6px;font-weight:bold;font-size:14px;">
                                Đặt lại mật khẩu
                            </a>
                        </td>
                    </tr>

                    <!-- Link dự phòng -->
                    <tr>
                        <td style="padding:0 24px 20px 24px;color:#555;font-size:13px;">
                            <p style="margin:0 0 8px 0;">Nếu nút không hoạt động, hãy copy link sau và dán vào trình duyệt:</p>
                            <p style="word-break:break-all;margin:0;">
                                <a href="{{ $resetUrl }}" style="color:#0d6efd;text-decoration:underline;">{{ $resetUrl }}</a>
                            </p>
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td style="padding:16px 24px;color:#999;font-size:12px;border-top:1px solid #eee;line-height:1.4;">
                            Liên kết sẽ hết hạn sau <strong>60 phút</strong> kể từ khi gửi.<br>
                            Nếu bạn không yêu cầu, vui lòng bỏ qua email này.<br><br>
                            &copy; {{ date('Y') }} {{ $appName }}. All rights reserved.
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>

</html>