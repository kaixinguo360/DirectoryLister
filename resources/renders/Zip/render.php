<?php

$name = $_GET['name'];

if (!empty($name)) {
    display_zip_content($lister->absPath($dir, $file), $name);
    exit;
}

$files = list_zip_contents($path, null, true);

$baseURL = $lister->getURL($dir, $file);
if ($render) {
    $baseURL = $baseURL . '&render=' . $render;
}

?>

<style>
.title {
    display: inline-block;
    margin-right: 8px;
}
.bar {
    display: flex;
    margin-bottom: 20px;
    align-items: center;
}
</style>

<div class="container readme-background">
    <div class="readme">
        <div class="bar">
            <h4 style="flex: 1 0; padding-bottom: 0;" class="title"><?php echo $file ?></h4>
            <a style="flex: 0 1 auto" href="<?php echo $baseURL . '&render=Default' ?>">
                <i class="fa fa-exchange"></i>
            </a>
        </div>
        <hr>
        <ul>
            <?php foreach($files as $name): ?>
                <li>
                    <a href="<?php echo $baseURL . '&name=' . rawurlencode($name) ?>" >
                        <?php echo $name ?>
                    </a>
                </li>
            <?php endforeach; ?>
        <ul>
    </div>
</div>

