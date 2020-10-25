<?php
function passedTime($date1, $dateFormat = 'Y-m-d H:i:s') {
    // Declare and define two dates 
    $savedDate = htmlspecialchars($date1);
    $date1 = strtotime($savedDate);
    $date2 = strtotime(date($dateFormat));
    // Formulate the Difference between two dates 
    $diff = abs($date2 - $date1);


    // To get the year divide the resultant date into 
    // total seconds in a year (365*60*60*24) 
    $years = floor($diff / (365 * 60 * 60 * 24));


    // To get the month, subtract it with years and 
    // divide the resultant date into 
    // total seconds in a month (30*60*60*24) 
    $months = floor(($diff - $years * 365 * 60 * 60 * 24)
        / (30 * 60 * 60 * 24));


    // To get the day, subtract it with years and  
    // months and divide the resultant date into 
    // total seconds in a days (60*60*24) 
    $days = floor(($diff - $years * 365 * 60 * 60 * 24 -
        $months * 30 * 60 * 60 * 24) / (60 * 60 * 24));


    // To get the hour, subtract it with years,  
    // months & seconds and divide the resultant 
    // date into total seconds in a hours (60*60) 
    $hours = floor(($diff - $years * 365 * 60 * 60 * 24
        - $months * 30 * 60 * 60 * 24 - $days * 60 * 60 * 24)
        / (60 * 60));


    // To get the minutes, subtract it with years, 
    // months, seconds and hours and divide the  
    // resultant date into total seconds i.e. 60 
    $minutes = floor(($diff - $years * 365 * 60 * 60 * 24
        - $months * 30 * 60 * 60 * 24 - $days * 60 * 60 * 24
        - $hours * 60 * 60) / 60);


    // To get the minutes, subtract it with years, 
    // months, seconds, hours and minutes  
    $seconds = floor(($diff - $years * 365 * 60 * 60 * 24
        - $months * 30 * 60 * 60 * 24 - $days * 60 * 60 * 24
        - $hours * 60 * 60 - $minutes * 60));

    $plural = '';
    if ($years > 0) {
        if ($years > 1) $plural = 's';
        return $years . ' year' . $plural;

    } elseif ($months > 0) {
        if ($months > 1) $plural = 's';
        return $months . ' month' . $plural;

    } elseif ($days > 0) {
        if ($days > 1) $plural = 's';
        return $days . ' day' . $plural;

    } elseif ($hours > 0) {
        if ($hours > 1) $plural = 's';
        return $hours . ' hour' . $plural;

    } elseif ($minutes > 0) {
        if ($minutes > 1) $plural = 's';
        return $minutes . ' minute' . $plural;

    } elseif ($seconds > 0) {
        if ($seconds > 1) $plural = 's';
        return $seconds . ' second' . $plural;
    } else {
        return $savedDate;
    }
}
?>