<?php

$size = count($data['files']);

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
    max-width: 100%;
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
#main {
    position: relative;
    display: flex;
    flex-flow: column;
    justify-content: center;
    align-items: center;
    width: 100vw;
    max-width: 600px;
    min-height: calc(100vh - 360px);
}
</style>

<script src="/resources/viewers/Image/js/lightense.min.js"></script>

<div id="container" class="container" onselectstart="return false">

    <!-- 列表 -->
    <div id="image-listing">

        <!-- 上一页按钮 -->
        <?php if (($data['start'] - 1) >= 0): ?>
            <a id="prevPage" class="button" href="<?= $data['prev'] ?>">
                <img src="<?= $data['files'][$data['start'] - 1] ?>" class="preview" />
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
        <?php for($i = $data['start']; $i < $data['end']; $i++): ?>
            <a onclick="changeImage(<?= $i ?>);">
                <img class="preview"
                    id="image-<?= $i ?>"
                    src="<?= $data['files'][$i] ?>" />
            </a>
        <?php endfor; ?>

        <!-- 下一页按钮 -->
        <?php if ($data['end'] < $size): ?>
            <a id="nextPage" class="button" href="<?= $data['next'] ?>">
                <img src="<?= $data['files'][$data['end']] ?>" class="preview" />
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
    <div id="info"><?= ($data['cur'] + 1) . '/' . $size ?></div>

    <!-- 正文 -->
    <div id="main">
        <a style="left: 0" class="in-button" onclick="changeImage(cur - 1)">
            <i style="background: unset" class="overlay fa fa-angle-left fa-4x"></i>
        </a>
		<img id="display" src="<?= $data['files'][$data['cur']] ?>" />
        <a style="right: 0" class="in-button" onclick="changeImage(cur + 1)">
            <i style="background: unset" class="overlay fa fa-angle-right fa-4x"></i>
        </a>
    </div>

</div>

<script>
const start = <?= $data['start'] ?>;
const end = <?= $data['end'] ?>;
const size = <?= $size ?>;
let cur = <?= $data['cur'] ?>;
function changeImage(id) {
    if (id < 0 || id >= size) return;
    if (id < start) {
        location.href = $('#prevPage').attr('href');
    }
    if (id >= end) {
        location.href = $('#nextPage').attr('href');
    }
    cur = id;
    const src = $(`#image-${id}`).attr('src');
    $('#display').attr('src', src);
    $('#display-link').attr('href', src);
    $('#info').html(`${id + 1}/${size}`);
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

