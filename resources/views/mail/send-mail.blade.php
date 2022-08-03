@component('mail::message')
# Introduction

Shiba inu - Husky

@component('mail::button', ['url' => 'http://149.102.143.50/campaign/create'])
Button Text
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent
