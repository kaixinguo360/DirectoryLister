<?php

$size = filesize($path);
if ($size > 10 * 1024 * 1024 && !$_GET['force']) {
    $error = "Unsupported file size: " . $lister->getFileSize($path);
} else {
    $type = mime_content_type($path);
    if (explode('/', $type)[0] != 'text' && !$_GET['force']) {
        $error = "Unsupported mime content type: " . $type;
    } else {
        $text = read_file_contents($path);
    }
}

?>

<style>
.title {
    font-size: 22px;
    margin-top: 20px;
    padding-bottom: 5px;
    font-weight: 500;
    line-height: 1.1;
    color: inherit;
}
.content {
    color: currentColor;
}
.bar {
    display: flex;
    margin-bottom: 20px;
    border-bottom: solid 1px #dadada;
    align-items: center;
}
.linebr {
    clear: both; /* 清除左右浮动 */
    word-break: break-word; /* 文本行的任意字内断开 */
    word-wrap: break-word; /* IE */
    white-space: -moz-pre-wrap; /* Mozilla */
    white-space: -hp-pre-wrap; /* HP printers */
    white-space: -o-pre-wrap; /* Opera 7 */
    white-space: -pre-wrap; /* Opera 4-6 */
    white-space: pre; /* CSS2 */
    white-space: pre-wrap; /* CSS 2.1 */
}
.warn {
    display: block;
    padding: 9.5px;
    margin: 0 28px 10px;
    font-size: 13px;
    color: #d62f2f;
    word-break: break-word;
    word-wrap: break-word;
}
</style>

<script>
function toggleWrap() {
    $('#content').toggleClass('linebr');
}
</script>

<div class="container readme-background">
    <div class="readme">
        <div class="bar">
            <div style="flex: 1 0 auto" class="title"><?php echo $file ?></div>
            <div style="flex: 0 1 auto" onclick="toggleWrap()">
                <i class="fa fa-exchange"></i>
            </div>
        </div>
        <?php if (!empty(error)): ?>
        <pre id="content" class="content linebr"><?php echo htmlentities($text) ?></pre>
        <?php else: ?>
        <div class="warn">
            [WARN] <?php echo htmlentities($error) ?>
            <a href="<?php echo $lister->getAppURL() . '?dir=' . $dir . '&file=' . $file . '&force=true' ?>"><u>[force]</u></a>
        </div>
        <?php endif ?>
    </div>
</div>

