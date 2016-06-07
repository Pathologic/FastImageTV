<?php

$config = array();
$config = array(
    "folder" => "assets/user_images/[+id+]/",
    "filename" => "[+month+]-[+day+]-[+filename+]",
    'allowed' => array('png','jpg','jpeg','gif'),
    "maxWidth" => 1920,
    "maxHeight" => 1080,
    "autoRotate" => true,
    "thumbnail" => array(
        "folder" => ".thumbs",
        "options" => "w=200&h=200&far=C&bg=FFFFFF"
    ),
    /*
    "previews" => array(
        "300x300" => array(
            "template" = > 1,
            "folder" => "300x300",
            "options" => "w=300&h=300&zc=C"
        ),
        "500x500" => array(
            "template" => 1,
            "folder" => "500x500",
            "options" => "w=500&h=500&zc=C"
        )
    )*/
);
return $config;