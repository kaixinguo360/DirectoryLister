<?php

$files = [];
$exts = 'bmp, gif, jpg, jpeg, png, psd, tga, tif';
$i = 0;
foreach($dirArray as $name => $fileInfo) {
    if (checkExtension($name, $exts)) {
        if ($file == $name) {
            $cur = $i;
        }
        $files[] = $name;
        $i++;
    }
}
$size = $i;

DEFINE('PAGE_SIZE', 10);

$page = floor($cur / PAGE_SIZE);
$start = max(0, $page * PAGE_SIZE);
$end = min($size - 1, ($page + 1) * PAGE_SIZE);

$prev = ($start <= 0) ? null : $files[$start - 1];
$next = ($end >= ($size - 1)) ? null : $files[$end];

?>

<style>
#container {
    display: flex;
    flex-flow: column;
    justify-content: center;
    margin: auto;
    align-items: center;
}
#display {
    width: 100%;
    max-width: 600px;
}
#image-listing {
    display: flex;
    justify-content: center;
    align-items: center;
    flex-wrap: wrap;
}
.preview {
    width: 72px;
    height: 54px;
    object-fit: cover;
}
#info {
    margin: 6px;
}
.button {
    position: relative;
    width: 72px;
    height: 54px;
}
.overlay {
    position: absolute;
    top: 0; bottom: 0; left: 0; right: 0;
    display: flex;
    justify-content: center;
    align-items: center;
    color: #ffffffd0;
    background: #00000060;
    z-index: 10;
}
.in-button {
    position: absolute;
    width: 72px;
    top: 0;
    bottom: 0;
    z-index: 10;
}
</style>

<script src="/resources/renders/Image/js/lightense.min.js"></script>

<div id="container" class="container">

    <!-- 列表 -->
    <div id="image-listing">

        <!-- 上一页按钮 -->
        <?php if (!empty($prev)): ?>
            <a id="prevPage" class="button" href="<?php echo $lister->getURL($dir, $prev); ?>">
                <img src="<?php echo $lister->absPath($dir) . '/' . $prev; ?>" class="preview" />
                <div class="overlay">
                    <i class="fa fa-angle-left fa-4x"></i>
                </div>
            </a>
        <?php else: ?>
            <a class="button" disabled>
                <i style="background: #00000020" class="overlay fa fa-angle-left fa-4x"></i>
            </a>
        <?php endif; ?>

        <!-- 本页内容 -->
        <?php for($i = $start; $i < $end; $i++): ?>
            <a id="image-<?php echo $i ?>"
                name="<?php echo $files[$i] ?>"
                onclick="changeImage(<?php echo $i ?>, '<?php echo $files[$i] ?>');">
                <img src="<?php echo $lister->absPath($dir) . '/' . $files[$i]; ?>" class="preview" />
            </a>
        <?php endfor; ?>

        <!-- 下一页按钮 -->
        <?php if (!empty($next)): ?>
            <a id="nextPage" class="button" href="<?php echo $lister->getURL($dir, $next); ?>">
                <img src="<?php echo $lister->absPath($dir) . '/' . $next; ?>" class="preview" />
                <div class="overlay">
                    <i class="fa fa-angle-right fa-4x"></i>
                </div>
            </a>
        <?php else: ?>
            <a class="button" disabled>
                <i style="background: #00000020" class="overlay fa fa-angle-right fa-4x"></i>
            </a>
        <?php endif; ?>

    </div>

    <!-- 页码 -->
    <div id="info"><?php echo ($cur + 1) . '/' . ($size - 1) ?></div>

    <!-- 正文 -->
    <div style="position: relative">
        <a style="left: 0" class="in-button" onclick="changeImage(cur - 1)">
            <i style="background: unset" class="overlay fa fa-angle-left fa-4x"></i>
        </a>
		<img id="display" src="<?php echo $lister->absPath($dir) . '/' . $file; ?>" />
        <a style="right: 0" class="in-button" onclick="changeImage(cur + 1)">
            <i style="background: unset" class="overlay fa fa-angle-right fa-4x"></i>
        </a>
    </div>

</div>

<script>
const root = "<?php echo '?dir=' . $dir . '&file=' ?>";
const absPath = "<?php echo $lister->absPath($dir) . '/' ?>";
const start = <?php echo $start ?>;
const end = <?php echo $end ?>;
const size = <?php echo $size ?>;
let cur = <?php echo $cur ?>;
function changeImage(id) {
    if (id < 0 || id >= size - 1) return;
    if (id < start) {
        location.href = $('#prevPage').attr('href');
    }
    if (id >= end) {
        location.href = $('#nextPage').attr('href');
    }
    cur = id;
    const name = $(`#image-${id}`).attr('name');
    $('#display').attr('src', absPath + name);
    $('#display-link').attr('href', absPath + name);
    $('#info').html(`${id + 1}/${size - 1}`);
}

const preventMove = (e) => e.preventDefault();

Lightense('#display', {
    time: 300,
    padding: 0,
    offset: 0,
    keyboard: false,
    cubicBezier: 'cubic-bezier(.2, 0, .1, 1)',
    background: 'rgba(255, 255, 255, .98)',
    zIndex: 2147483647,
    beforeShow: (config) => {
        document.body.addEventListener('touchmove', preventMove, { passive: false });
        document.addEventListener('onmousewheel', preventMove, { passive: false });
    },
    afterHide: (config) => {
        document.body.removeEventListener('touchmove', preventMove);
        document.removeEventListener('onmousewheel', preventMove);
    },
});
</script>

