<?php
function createdToText($datetime)
{
    $unixtimestamp = strtotime($datetime);
	return strftime("%B %d, %Y at %I:%M %p", $unixtimestamp);
}

function datetimeToText($datetime)
{
	return strftime("%B %d, %Y at %I:%M %p", $datetime);
}

function formatTime($datetime) {
	$unixtimestamp = strtotime($datetime);
	return strftime("%I:%M %p", $unixtimestamp);
}

function getToday() {
	$timezone = new DateTimeZone( "Africa/Cairo" );
	$date = new DateTime();
	$date->setTimezone( $timezone );
	$today = $date->format('Y-m-d');
	return $today;
}