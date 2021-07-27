<?php
include('Crypto.php');
error_reporting(0);
function decrypt_cc($encResponse)
{;
    $workingKey = 'FC01654505FECA8122A2AA2BE4837561';        //Working Key should be provided here.
    $rcvdString = decrypt($encResponse, $workingKey);        //Crypto Decryption used as per the specified working key.
    $decryptValues = explode('&', $rcvdString);
    $response = [];
    foreach ($decryptValues as $value) 
        $response[substr($value, 0, strpos($value, "="))] = substr($value, strpos($value, "=")+1);
    return $response;
}
