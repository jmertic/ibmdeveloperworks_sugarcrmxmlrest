<?php

// specify the REST web service to interact with
$url = 'http://localhost/sugarcrm/custom/service/v2/rest.php';
$username = 'username';
$password = md5('password');

// Open a curl session for making the call
$curl = curl_init($url);

// Tell curl to use HTTP POST
curl_setopt($curl, CURLOPT_POST, true);

// Tell curl not to return headers, but do return the response
curl_setopt($curl, CURLOPT_HEADER, false);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

// Set the POST arguments to pass to the Sugar server
$xml = <<<EOXML
<?xml version="1.0" encoding="UTF-8"?>
<parameters>
    <user_auth>
        <user_name>{$username}</user_name>
        <password>{$password}</password>
        <version>0.1</version>
    </user_auth>
    <application_name>test</application_name>
    <name_value_list />
</parameters>
EOXML;
$postArgs = array(
                'method' => 'login',
                'input_type' => 'xml',
                'response_type' => 'xml',
                'rest_data' => $xml
                );
curl_setopt($curl, CURLOPT_POSTFIELDS, $postArgs);

// Make the REST call, returning the result
$response = curl_exec($curl);

$xml = simplexml_load_string($response);

if ( !isset($xml->id) ) {
    die("Error: {$xml->name} - {$xml->description}\n.");
}

echo "Logged in successfully! Session ID is {$xml->id}\n";
