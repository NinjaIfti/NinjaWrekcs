@if(session('data_layer_event'))
<x-data-layer :payload="session('data_layer_event')" />
@endif
