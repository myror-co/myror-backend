@component('mail::message')
# Hi {{$first_name}}!

## Congrats you have a new booking request!

@component('mail::panel')
### Guest details
Name: **{{$first_name}}** **{{$last_name}}**  
Number of guests: **{{$guests}}**  
Email: **{{$email}}**  
Phone: **{{$phone}}**  
@endcomponent

@component('mail::panel')
### Booking details  
Room: **{{$listing_name}}**  
Check-in date: **{{$start}}**  
Check-out date: **{{$end}}**  
Message: **{{$message}}**   
 
@endcomponent

@component('mail::button', ['url' => 'mailto:'.$email, 'color' => 'success'])
Reply to {{$first_name}}
@endcomponent

Best,<br>
{{ config('app.name') }}
@endcomponent
