<?php

$files = scandir('resources/renders');

?>

<div class="container readme-background">
    <div class="readme">
        <h4>没有合适的渲染器</h4>
        <h5><?php echo $file ?></h5>
        <hr>
        <p>您可以尝试使用以下渲染器渲染此文件</p>
        <ul>
            <?php foreach($files as $f): ?>
                <?php if (substr($f, 0, 1) == '.') continue ?>
                <li>
                    <a href="<?php echo '?dir=' . $dir . '&file=' . $file . '&render=' . $f ?>" >
                        <?php echo $f ?>
                    </a>
                </li>
            <?php endforeach; ?>
        <ul>
    </div>
</div>

