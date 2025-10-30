<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Bảo trì hệ thống - {{ config('app.name', 'Shop Phụ Kiện') }}</title>

    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        :root {
            --primary-color: #f97316;
            /* Màu cam nổi bật (tương tự Tailwind orange-500) */
            --secondary-color: #1e293b;
            /* Màu chữ chính */
            --text-light: #64748b;
            /* Màu chữ phụ */
            --bg-light: #fef3c7;
            /* Nền nhẹ cho container */
            --bg-body: #fff7ed;
            /* Nền ngoài tổng thể */
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Roboto', sans-serif;
            background: var(--bg-body);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            text-align: center;
        }

        /* Container chính */
        .maintenance-container {
            max-width: 700px;
            width: 100%;
            background: var(--bg-light);
            border-radius: 20px;
            padding: 20px 80px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            border: 2px solid var(--primary-color);
            transform: scale(0.95);
            opacity: 0;
            animation: fadeInScale 0.8s forwards;
        }

        @keyframes fadeInScale {
            to {
                opacity: 1;
                transform: scale(1);
            }
        }

        /* Icon Nổi bật */
        .icon-wrapper {
            width: 100px;
            height: 100px;
            margin: 0 auto 30px;
            background: var(--primary-color);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 0 0 10px rgba(249, 115, 22, 0.3);
            /* Vòng sáng */
        }

        .icon-wrapper i {
            font-size: 3rem;
            color: #ffffff;
            animation: pulse 2s infinite;
            /* Hiệu ứng nhịp đập nhẹ */
        }

        @keyframes pulse {
            0% {
                transform: scale(1);
            }

            50% {
                transform: scale(1.1);
            }

            100% {
                transform: scale(1);
            }
        }

        /* Tiêu đề */
        h1 {
            font-size: 2.25rem;
            font-weight: 700;
            color: var(--secondary-color);
            margin-bottom: 10px;
            letter-spacing: -0.02em;
        }

        .subtitle {
            font-size: 1.15rem;
            color: var(--text-light);
            margin-bottom: 25px;
            font-weight: 500;
        }

        /* Mô tả chi tiết */
        p {
            font-size: 1rem;
            color: var(--text-light);
            line-height: 1.6;
            margin-bottom: 20px;
        }

        .highlight {
            color: var(--primary-color);
            font-weight: 700;
        }

        /* Thông tin chi tiết (Info Cards) */
        .info-cards {
            display: flex;
            justify-content: space-around;
            gap: 15px;
            margin: 30px 0;
        }

        .info-card {
            flex: 1;
            padding: 15px 10px;
            border-radius: 10px;
            background: #ffffff;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            transition: transform 0.3s ease;
        }

        .info-card:hover {
            transform: translateY(-5px);
        }

        .info-card i {
            font-size: 1.5rem;
            color: var(--primary-color);
            margin-bottom: 8px;
            display: block;
        }

        .info-card h3 {
            font-size: 0.9rem;
            color: var(--secondary-color);
            font-weight: 700;
            margin: 0;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        /* Contact Info */
        .contact-info {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid rgba(249, 115, 22, 0.2);
        }

        .contact-info p {
            font-size: 0.9rem;
            color: var(--text-light);
            margin-bottom: 15px;
        }

        .contact-link {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: var(--secondary-color);
            text-decoration: none;
            font-size: 1rem;
            font-weight: 500;
            padding: 8px 15px;
            border-radius: 8px;
            border: 1px solid var(--primary-color);
            transition: background-color 0.3s ease, color 0.3s ease;
        }

        .contact-link:hover {
            background-color: var(--primary-color);
            color: #ffffff;
        }

        .contact-link i {
            font-size: 1.1rem;
        }

        /* Responsive */
        @media (max-width: 600px) {
            .maintenance-container {
                padding: 40px 25px;
            }

            h1 {
                font-size: 1.75rem;
            }

            .subtitle {
                font-size: 1rem;
            }

            .info-cards {
                flex-direction: column;
                gap: 10px;
            }

            .contact-link {
                width: 100%;
                justify-content: center;
                margin-bottom: 10px;
            }
        }
    </style>
</head>

<body>
    <div class="maintenance-container">
        <div class="icon-wrapper">
            <i class="fas fa-hammer"></i>
        </div>

        <h1>Hệ thống đang được bảo trì</h1>
        <p class="subtitle">
            Chúng tôi đang nâng cấp để mang đến trải nghiệm tốt hơn!
        </p>

        <p>
            Trang web đang tạm thời ngừng hoạt động để thực hiện <span class="highlight">nâng cấp và bảo trì hệ thống định kỳ</span>.
            Mục tiêu là cải thiện hiệu suất, tính bảo mật và bổ sung các tính năng mới cho bạn.
        </p>

        <div class="info-cards">
            <div class="info-card">
                <i class="fas fa-hourglass-half"></i>
                <h3>Thời gian dự kiến</h3>
                <p style="margin-bottom: 0;">Sẽ hoạt động lại trong vòng <strong class="highlight">1-2 giờ tới</strong>.</p>
            </div>
            <div class="info-card">
                <i class="fas fa-lock"></i>
                <h3>Mục tiêu</h3>
                <p style="margin-bottom: 0;">Tối ưu hóa tốc độ và <strong class="highlight">tăng cường bảo mật</strong>.</p>
            </div>
        </div>

        <p style="margin-top: 10px;">
            Xin lỗi vì sự bất tiện này. Chúng tôi cam kết sẽ hoàn tất sớm nhất có thể.
        </p>

        <div class="contact-info">
            <p><strong>Cần hỗ trợ khẩn cấp?</strong> Vui lòng liên hệ qua:</p>
            <div style="display: flex; justify-content: center; gap: 15px; flex-wrap: wrap;">
                <a href="mailto:{{ $site_settings['contact_email'] ?? 'support@example.com' }}" class="contact-link">
                    <i class="fas fa-envelope"></i>
                    <span>Email hỗ trợ: {{ $site_settings['contact_email']}}</span>
                </a>
                <a href="tel:{{ $site_settings['contact_phone'] ?? '0123456789' }}" class="contact-link">
                    <i class="fas fa-phone"></i>
                    <span>Hotline: {{ $site_settings['contact_phone']}}</span>
                </a>
            </div>
        </div>
    </div>
</body>

</html>