<?php
/**
 * Плейсхолдеры в имени папки и файла:
 * [+id+] - id документа
 * [+parent+] - родитель документа
 * [+uid+] - id менеджера
 * [+rand+] - случайное значение
 * [+time+] - unix-время
 * [+year+] - год
 * [+month+] - месяц
 * [+day+] - день
 * [+hour+] - час
 * [+minute+] - минута
 * [+second+] - секунда
 * 
 */
$config = array();
$config = array(
    "folder" => "assets/user_images/[+id+]/",
    "filename" => "[+month+]-[+day+]-[+filename+]",
    "allowed" => array('png','jpg','jpeg','gif'),
    //настройки для преобразования изображения при загрузке
    "imageTransform" => array(
        "maxWidth" => 1920,
        "maxHeight" => 1080,
        "quality" => 0.93,
    ),
    //преобразовывать на клиенте если true, иначе на сервере
    "clientResize" => true,
    //превью в админке
    "thumbnail" => array(
        "folder" => ".thumbs",
        "options" => "w=200&h=200&far=C&bg=FFFFFF"
    ),/*
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