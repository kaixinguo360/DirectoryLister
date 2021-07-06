<!DOCTYPE html>

<?php header("Content-type: text/html; charset=utf-8") ?>
<?php $dir = $lister->getDir() ?>
<?php $file = $lister->getFile() ?>

<html>
    <head>
        <title><?php echo $lister->title . ($dir == '.' ? '' : (' / ' . str_replace('/', ' / ', $dir))); ?></title>
        <link rel="shortcut icon" href="/resources/themes/bootstrap/img/folder.png">
        <link rel="stylesheet" href="/resources/themes/bootstrap/css/bootstrap.min.css">
        <link rel="stylesheet" href="/resources/themes/bootstrap/css/font-awesome.min.css">
        <link rel="stylesheet" type="text/css" href="/resources/themes/bootstrap/css/style.css">
        <link href="/resources/themes/bootstrap/css/prism.css" rel="stylesheet" />
        <script src="/resources/themes/bootstrap/js/jquery.min.js"></script>
        <script src="/resources/themes/bootstrap/js/prism.js"></script>
        <script src="/resources/themes/bootstrap/js/bootstrap.min.js"></script>
        <!-- script type="text/javascript" src="/resources/themes/bootstrap/js/directorylister.js"></script -->
        <!-- link rel="stylesheet" type="text/css"  href="//fonts.googleapis.com/css?family=Cutive+Mono" -->
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
        <?php file_exists('analytics.inc') ? include('analytics.inc') : false; ?>
    </head>
    <body>
        <!-- 导航栏 -->
        <div id="page-navbar" class="navbar navbar-default navbar-fixed-top">
            <div class="container">
                <?php $breadcrumbs = $lister->listBreadcrumbs(); ?>
                <p class="navbar-text">
                    <?php foreach($breadcrumbs as $breadcrumb): ?>
                        <?php if ($breadcrumb != end($breadcrumbs)): ?>
                                <a href="<?php echo $breadcrumb['link']; ?>"><?php echo $breadcrumb['text']; ?></a>
                                <span class="divider">/</span>
                        <?php elseif (!empty($file)): ?>
                            <a href="<?php echo $breadcrumb['link']; ?>"><?php echo $breadcrumb['text']; ?></a>
                        <?php else: ?>
                            <?php echo $breadcrumb['text']; ?>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </p>
            </div>
        </div>

        <!-- 页眉 -->
        <?php file_exists($lister->getThemePath(true) . '/config/header.php')
            ? include($lister->getThemePath(true) . '/config/header.php')
            : include($lister->getThemePath(true) . "/config/default_header.php"); ?>

        <!-- 消息 -->
        <?php if($lister->getSystemMessages()): ?>
            <div class="container">
                <?php foreach ($lister->getSystemMessages() as $message): ?>
                    <div class="alert alert-<?php echo $message['type']; ?>">
                        <?php echo $message['text']; ?>
                        <a class="close" data-dismiss="alert" href="#">&times;</a>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <!-- 正文 -->
        <?php if (!$lister->getAuthStatus()): ?>
            <!-- 权限验证 -->
            <div class="container text-center" style="margin: 32px auto 48px auto">
                <i class="fa fa-4x fa-lock"></i>
                <div style="height: 32px"></div>
                <form method='post' action=''>
                    <div style='display: inline-flex'>
                        <input class='form-control' style='display: inline-block' type='password' name='password' />
                        <div style='width: 8px'></div>
                        <button class="btn btn-default">
                            <i class="fa fa-check"></i>
                        </button>
                    </div>
                </form>
            </div>
        <?php else: ?>

            <!-- 目录列表 -->
            <?php if (empty($_GET['file'])): ?>
                <div id="page-content" class="container" style='padding-right: 24px'>
                    <div id="directory-list-header">
                        <div class="row">
                            <div class="col-md-6 col-sm-5 col-xs-6">文件</div>
                            <div class="col-md-2 col-sm-2 col-xs-3 text-right">大小</div>
                            <div class="col-md-3 col-sm-4 hidden-xs text-right">最后修改时间</div>
                            <div class="col-md-1 col-sm-1 col-xs-3 text-right">
                                <i class="fa fa-info-circle"></i>
                            </div>
                        </div>
                    </div>
                    <ul id="directory-listing" class="nav nav-pills nav-stacked">
                        <?php foreach($dirArray as $name => $fileInfo): ?>
                            <li data-name="<?php echo $name; ?>" data-href="<?php echo $fileInfo['url_path']; ?>">
                                <a href="<?php echo $fileInfo['url_path']; ?>" class="clearfix" data-name="<?php echo $name; ?>">
                                    <div class="row">
                                        <span class="file-name col-md-6 col-sm-5 col-xs-6">
                                            <i class="fa <?php echo $fileInfo['icon']; ?> fa-fw"></i>
                                            <?php echo $name; ?>
                                        </span>
                                        <span class="file-size col-md-2 col-sm-2 col-xs-3 text-right">
                                            <?php echo $fileInfo['size']; ?>
                                        </span>
                                        <span class="file-modified col-md-3 col-sm-4 hidden-xs text-right">
                                            <?php echo $fileInfo['time']; ?>
                                        </span>
                                    </div>
                                </a>
                                <a href="<?php echo $lister->getURL($dir, $name); ?>"
                                    style='transform: translateX(-21px) translateX(-50%);'
                                    class="web-link-button" <?php if($lister->externalLinksNewWindow()): ?>target="_blank"<?php endif; ?>>
                                    <?php if ($fileInfo['renderable']): ?>
                                        <i class="fa fa-info-circle"></i>
                                    <?php else: ?>
                                        <i class="fa fa-info" style="color: lightgray"></i>
                                    <?php endif; ?>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <!-- 文件内容展示 -->
            <?php $lister->renderFile($_GET['render']); ?>

        <?php endif; ?>

        <!-- 页脚 -->
        <?php file_exists($lister->getThemePath(true) . '/config/footer.php')
            ? include($lister->getThemePath(true) . '/config/footer.php')
            : include($lister->getThemePath(true) . "/config/default_footer.php"); ?>
    </body>
</html>
