<?php
//var_dump(DIR_IMAGE);
$url = $argv[1];
$fileName = $argv[2];
$variant = $argv[3];
$dir_image = $argv[4];

//for stable working file_get_contents
$context = stream_context_create(
    array(
        "http" => array(
            "header" => "User-Agent: Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/50.0.2661.102 Safari/537.36"
        )
    )
);
$file = str_replace('.jpg', '_' . $variant . '.jpg', $url);
$data = file_get_contents($url, false, $context);
$new = "{$dir_image}/catalog/fromshopify/{$fileName}_{$variant}.jpg";
file_put_contents($new, $data);
//exec("curl {$url} > {$dir_image}/catalog/fromshopify/{$fileName}_{$variant}.jpg 2>&1");