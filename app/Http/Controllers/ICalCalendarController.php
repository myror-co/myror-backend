<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;


class iCalCalendarController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function get(Request $request, $id)
    {
        $listing = \App\Models\Listing::find($id);

        if (!$listing) 
        {
            return response()->json(['message' => 'Listing not found'], 404);
        }

        if($listing->security_key != $request->input('s'))
        {
            return response()->json(['message' => 'You are not allowed to access this resource'], 401);
        }

        $bookings = \App\Models\Booking::where('listing_id', $listing->id)->get();

        define('ICAL_FORMAT', 'Ymd\THis\Z');

        $icalObject = "BEGIN:VCALENDAR
        VERSION:2.0
        METHOD:PUBLISH
        PRODID:-//Myror//Bookings//EN\n";

        // loop over events
        foreach ($bookings as $booking) {
            $summary = '(direct)-'.$booking->first_name.','.$booking->last_name.'-'.$booking->guests.' guests';

            $icalObject .=
            "BEGIN:VEVENT
            DTSTART:" . date(ICAL_FORMAT, strtotime($booking->checkin)) . "
            DTEND:" . date(ICAL_FORMAT, strtotime($booking->checkout)) . "
            DTSTAMP:" . date(ICAL_FORMAT, strtotime($booking->created_at)) . "
            SUMMARY: $summary
            UID:$booking->uuid
            STATUS:" . strtoupper($booking->status) . "
            LAST-MODIFIED:" . date(ICAL_FORMAT, strtotime($booking->updated_at)) . "
            END:VEVENT\n";
        }

        // close calendar
        $icalObject .= "END:VCALENDAR";

        // Set the headers
        header('Content-type: text/calendar; charset=utf-8');
        header('Content-Disposition: attachment; filename="cal.ics"');

        $icalObject = str_replace(' ', '', $icalObject);

        echo $icalObject;
    }
}
