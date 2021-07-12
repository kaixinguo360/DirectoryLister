<?php

$content = parse_ini_file($path);
$url = $content['URL'];

display_with_viewer('URL', array(
    'url' => $url,
));

