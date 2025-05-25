@component('mail::message')
# Low Stock Alert

The drug {{ $drug->name }} is running low in stock.

Current Stock: {{ $drug->stock }}


@endcomponent

Thanks,
{{ config('app.name') }}
@endcomponent