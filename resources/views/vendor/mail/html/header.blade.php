@php
$appUrl = config('app.url');
$appName = config('app.name');
// Prefer explicit config/env, else fallback to existing public asset
$logoUrl = config('app.logo_url') ?: env('APP_LOGO_URL');
if (empty($logoUrl)) {
// Use absolute URL to public/img/logo_shop.png (exists in this project)
$logoUrl = url('img/logo_shop.png');
}

// Try to embed as CID (best for email clients), else Base64, else URL
$logoCid = null;
$logoDataUri = null;
try {
// Map URL to local path if pointing to our app URL
$possibleLocal = null;
if (str_contains($logoUrl, '/img/logo_shop.png')) {
$possibleLocal = public_path('img/logo_shop.png');
}
// If a local file exists, embed it
if ($possibleLocal && file_exists($possibleLocal)) {
// Prefer CID embedding if $message is available
if (isset($message)) {
$logoCid = $message->embed($possibleLocal);
} else {
// Fallback to Base64
$mime = mime_content_type($possibleLocal) ?: 'image/png';
$data = base64_encode(file_get_contents($possibleLocal));
if ($data) {
$logoDataUri = 'data:' . $mime . ';base64,' . $data;
}
}
}
} catch (\Throwable $e) {
// Silently ignore and fallback to URL
}
@endphp
<tr>
    <td class="header" style="text-align:center; padding: 25px 0;">
        <a href="{{ $appUrl }}" style="display:inline-block; text-decoration:none; color:#3d4852;">
            @if(!empty($logoCid))
            <img src="{{ $logoCid }}" alt="{{ $appName }}" style="height: 54px; display:block; margin:0 auto 8px;">
            @elseif(!empty($logoDataUri))
            <img src="{{ $logoDataUri }}" alt="{{ $appName }}" style="height: 54px; display:block; margin:0 auto 8px;">
            @elseif(!empty($logoUrl))
            <img src="{{ $logoUrl }}" alt="{{ $appName }}" style="height: 54px; display:block; margin:0 auto 8px;">
            @endif
            <div style="font-weight:700; font-size:20px;">{{ $slot ?: $appName }}</div>
        </a>
    </td>
</tr>