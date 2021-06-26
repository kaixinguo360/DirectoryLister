<?php
require_once('lib/CommonUtils.php');
$content = read_file_contents($path);
?>

<div class="container readme-background">
    <div class="readme">
        <?php echo $content ?>
    </div>
</div>

