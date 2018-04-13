<?php
/**
 * Placeholders available in the folder and file name:
 *
 * [+id+] - document id
 * [+parent+] - parent id
 * [+uid+] - user id
 * [+rand+] - random value
 * [+time+] - unix-time
 * [+year+] - year
 * [+month+] - month
 * [+day+] - day
 * [+hour+] - hour
 * [+minute+] - minute
 * [+second+] - second
 * 
 */
$config = array();
$config = array(
    "folder" => "assets/user_images/[+id+]/",
    "filename" => "[+month+]-[+day+]-[+filename+]",
    "allowed" => array('png','jpg','jpeg','gif'),
    
    // settings for image conversion at boot time
    "imageTransform" => array(
        "maxWidth" => 1920,
        "maxHeight" => 1080,
        "quality" => 0.93,
    ),
    
    // convert on the client side if true, otherwise on the server
    "clientResize" => true,
    
    // preview in the Manager
    "thumbnail" => array(
        "folder" => ".thumbs",
        "options" => "w=200&h=200&far=C&bg=FFFFFF"
    )/*,
    "previews" => array(
        "300x300" => array(
            "folder" => "300x300",
            "options" => "w=300&h=300&zc=C"
        ),
        "500x500" => array(
            "folder" => "500x500",
            "options" => "w=500&h=500&zc=C"
        )
    )*/
);
return $config;