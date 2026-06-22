<script>window.dataLayer = window.dataLayer || [];</script>
<!-- Google Tag Manager -->
<script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
})(window,document,'script','dataLayer','GTM-NRSV8VFV');</script>
<!-- End Google Tag Manager -->

<script>
window.dataLayer = window.dataLayer || [];
window.dataLayer.push({
  event: 'page_view',
  page_title: document.title,
  page_location: @json(url()->current()),
  page_path: @json(request()->path() === '' ? '/' : '/' . request()->path())
});
</script>

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

<!-- Meta Pixel Code -->
<script>
  !function(f,b,e,v,n,t,s)
  {if(f.fbq)return;n=f.fbq=function(){n.callMethod?
  n.callMethod.apply(n,arguments):n.queue.push(arguments)};
  if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';
  n.queue=[];t=b.createElement(e);t.async=!0;
  t.src=v;s=b.getElementsByTagName(e)[0];
  s.parentNode.insertBefore(t,s)}(window, document,'script',
  'https://connect.facebook.net/en_US/fbevents.js');
  fbq('init', '1021339323738428');
  fbq('track', 'PageView');
</script>
<!-- End Meta Pixel Code -->

@include('components.data-layer-flash')
