@if(config('services.google.analytics_id') && app()->environment('production'))
<!-- Google Analytics (GA4) -->
<script async src="https://www.googletagmanager.com/gtag/js?id={{ config('services.google.analytics_id') }}"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());
  gtag('config', '{{ config('services.google.analytics_id') }}', {
    'cookie_flags': 'SameSite=None;Secure'
  });
</script>
@endif
