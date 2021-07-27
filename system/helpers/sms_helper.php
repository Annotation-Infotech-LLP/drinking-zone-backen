<?php
// this is not the system helper and it is manually created by Shameel Salih
function sendSMS($mobile,$message)
{
    $message  = urlencode($message);
    $curl = curl_init();
    
    curl_setopt_array($curl, array(
      CURLOPT_URL => "https://api.msg91.com/api/sendhttp.php?mobiles=$mobile&authkey=289820AIZmOZhOSW5d569324&route=4&sender=DRNKZN&message=$message&country=91",
    
      // https://api.msg91.com/api/sendhttp.php?campaign=&response=&afterminutes=&schtime=&flash=&unicode=&mobiles=Mobile%20no.&authkey=%24authentication_key&route=4&sender=TESTIN&message=Hello!%20This%20is%20a%20test%20message&country=91
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => "",
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 30,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => "GET",
      CURLOPT_SSL_VERIFYHOST => 0,
      CURLOPT_SSL_VERIFYPEER => 0,
    ));
    
    $response = curl_exec($curl);
    $err = curl_error($curl);
    
    curl_close($curl);
    
    if ($err)
      return FALSE;
    return TRUE;
}