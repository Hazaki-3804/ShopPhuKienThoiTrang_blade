<div class="cf-turnstile" data-sitekey="{{ config('services.cloudflare-turnslite.site_key') }}"></div>
<div class="pb-2">
    @error('captcha')
    <x-input-error :message="$message" />
    @enderror
</div>

<script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
