<?php
function cryptPass($password)
{
    $encr = "$%&*)@H*$".sha1("shameel".$password."salih").")@(&GHDLKS"; // encripting the password
    return md5($encr."./+)(!?><>".$encr); 
}

function cover_me($string, $action = 'e') 
{
    $secret_key 	= "i+am&shameel&*@()_(&!salih@\'\"gew873*>?<"; // 32bits

    $secret_iv 		= md5('k(?9shameelsalih@3@#*');
    $secret_iv 		= md5($secret_iv);

    $output 		= false;
    $encrypt_method = "AES-256-CBC";
    $key 			= hash('sha256', $secret_key);
    $iv 			= substr(hash('sha256', $secret_iv), 0, 16);

 
    if ($action == 'e') {
        $output = base64_encode(openssl_encrypt($string, $encrypt_method, $key, 0, $iv));
    } elseif ($action == 'd') {
        $output = openssl_decrypt(base64_decode($string), $encrypt_method, $key, 0, $iv);
    }

    return $output;
}
function generateRandomString($length)
{
    $tockens    =   array("5","G","3","H","I","J","K","4","L","A","B","1","C","D","9","E","T","7","U","V","0","W","2","F","M","N","O","Y","6","Z","8","P","Q","R","S","X");
    $output="";
    while($length>0){
        $output.=$tockens[rand(0,35)];
        $length--;
    }
    return $output;
} 