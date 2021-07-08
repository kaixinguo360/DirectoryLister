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
 * 自动转换编码至UTF-8
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


/**
 * 合并多个路径片段
 */
function concat_path(...$args) {
    foreach ($args as $arg) {
        $arg = trim($arg, '\\');
        if (empty($arg) || $arg == '.') {
            continue;
        } else {
            if (empty($path)) {
                $path = $arg;
            } else {
                $path .= '/' . $arg;
            }
        }
    }
    return $path;
}


/**
 * 查找并返回指定文件的附加文件 (如某个剧集的字幕/弹幕等)
 * @param string $path 指定文件的路径
 * @param string $exts 附加文件的扩展名, 逗号分隔
 * @param string $dirs 附加文件搜索路径, 逗号分隔, 默认为仅搜索指定文件的同级目录
 * @param string $fuzzySearch 是否启用模糊搜索, 默认开启, 将文件名含有的数字识别为序列号并进行模糊匹配
 * @return string 附加文件的路径, 无匹配项时返回空
 */
function find_additional_resource($path, $exts, $dirs = '.', $fuzzySearch = true) {
    $info = pathinfo($path);

    $filePath = $path;
    $fileDir = $info['dirname'];
    $fileExt = $info['extension'];
    $fileFullName = $info['basename'];
    $fileName = $info['filename'];

    $dirs = explode(',', $dirs);
    $exts = explode(',', $exts);

    foreach ($dirs as $dir) {
        $dir = trim($dir);
        $resourcePath = concat_path($fileDir, $dir);
        if (!file_exists($resourcePath)) {
            continue;
        }
        foreach ($exts as $ext) {
            $ext = ltrim(trim($ext), '.');
            if (file_exists($fullPath = concat_path($resourcePath, $fileName . '.' . $ext))) {
                return $fullPath;
            } else if (file_exists($fullPath = concat_path($resourcePath, $fileFullName . '.' . $ext))) {
                return $fullPath;
            }
        }
    }

    if ($fuzzySearch) {
        $fileList = scandir($fileDir);
        $fileSriNo = 0;
        foreach ($fileList as $file) {
            if (pathinfo($file, PATHINFO_EXTENSION) == $fileExt) {
                $fileSriNo++;
            }
            if (pathinfo($file, PATHINFO_BASENAME) == $fileFullName) {
                break;
            }
        }
        foreach ($dirs as $dir) {
            $dir = trim($dir);
            $resourcePath = concat_path($fileDir, $dir);
            if (!file_exists($resourcePath)) {
                continue;
            }
            foreach ($exts as $ext) {
                $ext = ltrim(trim($ext), '.');
                $results = glob($resourcePath . '/*' . $fileSriNo . '*.' . $ext);
                $resultCount = count($results);
                if ($resultCount == 0) {
                    break;
                }
                if ($resultCount > 1) {
                    foreach ($results as $result) {
                        $name = pathinfo($file, PATHINFO_BASENAME);
                        $resultIndex = preg_replace('/\D/', '', $name) + 0;
                        if ($resultIndex == $fileSriNo) {
                            return $result;
                        }
                    }
                }
                return end($results);
            }
        }
    }

    return null;
}


/**
 * 检查传入的文件名是否匹配指定后缀
 * @param string $file 待检查文件名
 * @param string $exts 预期的扩展名, 逗号分隔
 * @return boolean 是否匹配
 */
function check_extension($file, $exts) {
    $ext = pathinfo($file, PATHINFO_EXTENSION);
    $exts = explode(',', $exts);
    foreach ($exts as $e) {
        if ($ext == trim($e)) {
            return true;
        }
    }
    return false;
}


/**
 * 展示Zip内文件, 直接输出至客户端, 完成后exit, 终止程序运行
 * Copy From Internet
 * @param string $path 压缩文件路径
 * @param string $name 压缩文件内文件的路径
 */
function display_zip_content($path, $name) {

    // 清除缓冲区
    ob_clean();

    // 获得为协议路径
    $path = 'zip://' . $path . '#' . $name;
    $file = basename($path);

    /**
     * 这里应该加上安全验证之类的代码，例如：检测请求来源、验证UA标识等等
     */

    // 以只读方式打开文件，并强制使用二进制模式
    $fileHandle=fopen($path, "rb");
    if($fileHandle===false){
        http_response_code(405);
        exit("Can not open file: $file");
    }

    // 设置缓存
    header("Cache-Control: public, max-age=315360000, immutable");

    // 文件大小
    //header("Content-Length: " . filesize($path));

    // 文件类型
    header("Content-Type: " . mime_content_type($path));
    header("Accept-Ranges: bytes");

    // 文件名
    header('Content-Disposition: inline; filename="' . urlencode($file) . '"');

    // 循环读取文件内容，并输出
    while(!feof($fileHandle)) {
        // 从文件指针 handle 读取最多 length 个字节（每次输出10k）
        echo fread($fileHandle, 10240);
    }

    // 关闭文件流
    fclose($fileHandle);

    // 结束输出
    exit;
}

