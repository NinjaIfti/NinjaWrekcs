@props(['payload'])

<script>
window.dataLayer = window.dataLayer || [];
window.dataLayer.push(@json($payload, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT));
</script>
