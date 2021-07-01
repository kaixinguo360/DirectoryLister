<?php
require_once("lib/Parsedown.php");

$Parsedown = new Parsedown();
$content = read_file_contents($path);
$md_text = $Parsedown->text($content);
?>

<div class="container readme-background">
    <div class="readme">
        <?php echo $md_text ?>
    </div>
</div>

