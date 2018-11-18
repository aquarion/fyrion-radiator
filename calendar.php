<?php

require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/lib/gcal.lib.php';
require __DIR__ . '/lib/radiator.lib.php';
define('SEND_JSON_ERRORS', True);

// Get the API client and construct the service object.
$client = getClient();
$service = new Google_Service_Calendar($client);

// Print the next events on the user's calendar.
$calendarId = $_GET['cal'];
$optParams = array(
  'orderBy' => 'startTime',
  'singleEvents' => true,
  'timeMin' =>  date('c', strtotime($_GET['start'])),
  'timeMax' =>  date('c', strtotime($_GET['end'])) 
);


$results = $service->events->listEvents($calendarId, $optParams);
$events = $results->getItems();

$events_out = array();

/* ,
    {
        "allDay": "",
        "title": "Test event",
        "id": "821",
        "end": "2011-06-06 14:00:00",
        "start": "2011-06-06 06:00:00"
    } */

foreach ($events as $event) {

    $start = $event->start->dateTime ? $event->start->dateTime : $event->start->date;
    $end = $event->end->dateTime ? $event->end->dateTime : $event->end->date;

	$events_out[] = array(
        "allDay" => $event->start->date ? true : false,
        "title"  => $event->summary,
        "id"     => $event->id,
        "end"    => $end,
        "start"  => $start
    );
}

header('content-type: text/json');
echo json_encode($events_out);