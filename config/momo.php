<?php

return [
    'partner_code' => env('MOMO_PARTNER_CODE', ''),
    'access_key' => env('MOMO_ACCESS_KEY', ''),
    'secret_key' => env('MOMO_SECRET_KEY', ''),
    'endpoint' => env('MOMO_ENDPOINT', 'https://test-payment.momo.vn/v2/gateway/api/create'),
    'return_url' => env('MOMO_RETURN_URL', env('APP_URL') . '/checkout/momo/return'),
    'notify_url' => env('MOMO_NOTIFY_URL', env('APP_URL') . '/checkout/momo/notify'),
    'redirect_url' => env('MOMO_REDIRECT_URL', env('APP_URL') . '/checkout/momo/return'),
];
