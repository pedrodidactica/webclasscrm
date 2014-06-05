<?php

if(!function_exists('its4you_unsubscribeemail')){
function its4you_unsubscribeemail($accountid,$contactid,$url_address,$label)
{
    global $site_URL;
    
    $url = $site_URL;
    $link = "";
    $u = "";
    if ($accountid != "" && $accountid != "0")
    {
        $u = $accountid;     
    }
    elseif ($contactid != "" && $contactid != "0")
    {
        $u = $contactid;     
    }

    $code = md5($crmid.$url);
    $small_code = substr($code, 5, 6);


    if ($u != "") $link = "<a href='".$url_address."?u=".$u."&c=".$small_code."'>".$label."</a>";

    return $link;
}
}
?>
