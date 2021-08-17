@component('mail::message')
# Hi {{$first_name}}!

## Your website {{$website_name}} is now ready!

The latest version of your website has been deployed to our hosting server.
You can access it anytime [{{$website_url}}]({{$website_url}}).

@component('mail::button', ['url' => $website_url, 'color' => 'success'])
View my website
@endcomponent

Best,<br>
{{ config('app.name') }}
@endcomponent
