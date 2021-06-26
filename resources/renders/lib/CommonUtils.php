<?php

define('UTF32_BIG_ENDIAN_BOM', chr(0x00) . chr(0x00) . chr(0xFE) . chr(0xFF));
define('UTF32_LITTLE_ENDIAN_BOM', chr(0xFF) . chr(0xFE) . chr(0x00) . chr(0x00));
define('UTF16_BIG_ENDIAN_BOM', chr(0xFE) . chr(0xFF));
define('UTF16_LITTLE_ENDIAN_BOM', chr(0xFF) . chr(0xFE));
define('UTF8_BOM', chr(0xEF) . chr(0xBB) . chr(0xBF));


/**
 * 读取文件内容, 并自动转换编码
 * Copy From Internet
 */
function read_file_contents($file) {
    $raw = file_get_contents($file);
    return to_utf8($raw);
}


/**
 * 自动转换编码
 * Copy From Internet
 */
function to_utf8($raw) {

    // UTF-8
    if (mb_check_encoding($raw, "UTF-8")) {
        return $raw;
    }

    // GBK
    if (mb_check_encoding($raw, "GBK")) {
        $content = iconv("GBK", "UTF-8", $raw);
        if ($content) {
            return $content;
        }
    }

    // Detect 1
    $encodType = mb_detect_encoding($raw);
    if ($encodType) {
        $content = iconv($encodType, "UTF-8", $raw);
        if ($content) {
            return $content;
        }
    }

    // Detect 2
    $first2 = substr($raw, 0, 2);
    $first3 = substr($raw, 0, 3);
    $first4 = substr($raw, 0, 3);

    if (UTF8_BOM == $first3) {
        $encodType = 'UTF-8 BOM';
    } else if (UTF32_BIG_ENDIAN_BOM == $first4) {
        $encodType = 'UTF-32BE';
    } else if (UTF32_LITTLE_ENDIAN_BOM == $first4) {
        $encodType = 'UTF-32LE';
    } else if (UTF16_BIG_ENDIAN_BOM == $first2) {
        $encodType = 'UTF-16BE';
    } else if (UTF16_LITTLE_ENDIAN_BOM == $first2) {
        $encodType = 'UTF-16LE';
    }

    //下面的判断主要还是判断ANSI编码的·
    if ('' == $encodType) {
        //即默认创建的txt文本-ANSI编码的
        $content = iconv("GBK", "UTF-8", $raw);
    } else if ('UTF-8 BOM' == $encodType) {
        //本来就是UTF-8不用转换
        $content = $raw;
    } else {
        //其他的格式都转化为UTF-8就可以了
        $content = iconv($encodType, "UTF-8", $raw);
    }

    if ($content) {
        return $content;
    } else {
        return $raw;
    }
}

