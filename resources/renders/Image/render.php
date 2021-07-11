<?php

$files = [];
$exts = 'bmp, gif, jpg, jpeg, png, psd, tga, tif';
$i = 0;
$cur = 0;
foreach($dirArray as $name => $fileInfo) {
    if (check_extension($name, $exts)) {
        if ($file == $name) {
            $cur = $i;
        }
        $names[] = $name;
        $files[] = $lister->absPath($dir, $name);
        $i++;
    }
}
$size = $i;

DEFINE('PAGE_SIZE', 10);

$page = floor($cur / PAGE_SIZE);
$start = max(0, $page * PAGE_SIZE);
$end = min($size, ($page + 1) * PAGE_SIZE);

$prev = ($start <= 0) ? null : $files[$start - 1];
$next = ($end >= ($size - 1)) ? null : $files[$end];

$prevURL = $lister->getURL($dir, $names[$start - 1]);
$nextURL = $lister->getURL($dir, $names[$end]);

display_with_viewer('Image', array(

    'files' => $files,

    'cur'   => $cur,
    'start' => $start,
    'end'   => $end,

    'next'  => $nextURL,
    'prev'  => $prevURL,

));

