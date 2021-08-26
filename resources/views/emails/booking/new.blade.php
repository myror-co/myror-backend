@component('mail::message')
# Hi {{$booking->first_name}}!

## Congrats you have a new direct booking!

@component('mail::panel')
### Guest details
Name: **{{$booking->first_name}}** **{{$booking->last_name}}**  
Number of guests: **{{$booking->guests}}**  
Email: **{{$booking->email}}**  
Phone: **{{$booking->phone}}**  
@endcomponent

@component('mail::panel')
### Booking details  
Total amount paid: **{{$booking->currency.' '.$booking->gross_amount}}**  
Payment method: **{{$booking->gateway == 'paypal' ? 'Paypal' : 'Debit/credit card'}}**  
Room: **{{$booking->listing->name}}**    
Check-in date: **{{\Carbon\Carbon::parse($booking->checkin)->toFormattedDateString()}}**   
Check-out date: **{{\Carbon\Carbon::parse($booking->checkout)->toFormattedDateString()}}**   
 
@endcomponent

@component('mail::button', ['url' => 'https://app.myror.co/bookings', 'color' => 'success'])
See more details
@endcomponent

Best,<br>
{{ config('app.name') }}'s team
@endcomponent
