<?php

$baseUrl = 'https://recycleapp.be/api/app/v1';

$secret = 'Crgja3EGWe8jdapyr4EEoMBgZACYYjRRcRpaMQrLDW9HJBvmgkfGQyYqLgeXPavAGvnJqkV87PBB2b8zx43q46sUgzqio4yRZbABhtKeagkVKypTEDjKfPgGycjLyJTtLHYpzwJgp4YmmCuJZN9ZmJY8CGEoFs8MKfdJpU9RjkEVfngmmk2LYD4QzFegLNKUbcCeAdEW';
$consumer = 'recycleapp.be';

$subscribers = json_decode(file_get_contents("locations.json"), true);

// Get auth token
$ch = curl_init("$baseUrl/access-token");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
curl_setopt($ch, CURLOPT_HTTPHEADER, array(
    'x-consumer: ' . $consumer,
    'x-secret: ' . $secret
));

$response = curl_exec($ch);

if (curl_error($ch)){
    echo 'Cannot get auth code';
    die;
}

curl_close($ch);
$access_token = json_decode($response)->accessToken;


// Get collections
$tomorrow = date('Y-m-d', strtotime('tomorrow'));
// $tomorrow = date('Y-m-d');

foreach($subscribers as $subscriber){
    $zipcodeID = $subscriber['zipcodeID'];
    $streetID = $subscriber['streetID'];
    $houseNumber = $subscriber['houseNumber'];

    $ch = curl_init("$baseUrl/collections?zipcodeId=$zipcodeID&streetId=$streetID&houseNumber=$houseNumber&fromDate=$tomorrow&untilDate=$tomorrow&size=100");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'x-consumer: ' . $consumer,
        'authorization: ' . $access_token
    ));

    $response = curl_exec($ch);

    if (curl_error($ch)){
        echo 'Cannot get collections';
        die;
    }

    curl_close($ch);
    $json = json_decode($response);

    $collections = [];  
    foreach($json->items as $event){
        if ($event->type == 'collection'){
            $collections[] = $event->fraction->name->nl;
        }
    }

    if (count($collections) > 0){
        $subject = 'Vuilnis ophaling ' . implode(' - ', $collections);
        $message = "Opgelet: er is morgen vuilnisophaling. Vergeet niet om alles buiten te zetten.\n\n";
        foreach ($collections as $collection){
            $message.= '- '. $collection . "\n";
        }

        if (!isset($_GET['test'])){
            mail($email, $subject, $message, "FROM: mail@kilianhendrickx.be");
        } else {
            echo "<b>$subject</b>";
            echo nl2br($message);
        }
        
    }

    

    
}

