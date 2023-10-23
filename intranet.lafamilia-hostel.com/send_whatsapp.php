<?php

function send_whatsapp($message="Test"){
    $phone="+41763688050";  // Enter your phone number here
    $apikey="123456";       // Enter your personal apikey received in step 3 above

    $url='https://api.callmebot.com/whatsapp.php?source=php&phone='.$phone.'&text='.urlencode($message).'&apikey='.$apikey;

    if($ch = curl_init($url))
    {
        curl_setopt ($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, 0);
        $html = curl_exec($ch);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        // echo "Output:".$html;  // you can print the output for troubleshooting
        curl_close($ch);
        return (int) $status;
    }
    else
    {
        return false;
    }
}


send_whatsapp("Löhn zahle. Fründlichi Grüess, am Patrick sin lässe Roboter."); 

?>