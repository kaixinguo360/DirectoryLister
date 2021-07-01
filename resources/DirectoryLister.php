<?php

/**
 * A simple PHP based directory lister that lists the contents
 * of a directory and all it's sub-directories and allows easy
 * navigation of the files within.
 *
 * This software distributed under the MIT License
 * http://www.opensource.org/licenses/mit-license.php
 *
 * More info available at http://www.directorylister.com
 *
 * @author Chris Kankiewicz (http://www.chriskankiewicz.com)
 * @copyright 2015 Chris Kankiewicz
 */
class DirectoryLister {

    // 定义应用程序版本
    const VERSION = '2.6.1';

    // Reserve some variables
    public $title             = null;
    protected $_themeName     = null;
    protected $_baseDir       = null;
    protected $_directory     = null;
    protected $_file          = null;
    protected $_onlyRender    = null;
    protected $_appDir        = null;
    protected $_appURL        = null;
    protected $_config        = null;
    protected $_fileTypes     = null;
    protected $_fileRenders   = null;
    protected $_systemMessage = null;


    /**
     * DirectoryLister construct function. Runs on object creation.
     */
    public function __construct() {

        // 设置class目录常量
        if(!defined('__DIR__')) {
            define('__DIR__', dirname(__FILE__));
        }

        // 设置应用程序目录
        $this->_appDir = __DIR__;

        // 构建应用程序URL
        $this->_appURL = $this->_getAppUrl();

        // 加载基本配置
        $this->_config = require_once($this->_appDir . '/config/config.default.php');
        if (file_exists($this->_appDir . '/config/config.php')) {
            $this->_config = array_merge($this->_config,
                require_once($this->_appDir . '/config/config.php'));
        }

        // 加载文件类型配置
        $this->_fileTypes = require_once($this->_appDir . '/config/fileTypes.default.php');
        if (file_exists($this->_appDir . '/config/fileTypes.php')) {
            $this->_fileTypes = array_merge($this->_fileTypes,
                require_once($this->_appDir . '/config/fileTypes.php'));
        }

        // 加载文件渲染器配置
        $this->_fileRenders = require_once($this->_appDir . '/config/fileRenders.default.php');
        if (file_exists($this->_appDir . '/config/fileRenders.php')) {
            $this->_fileRenders = array_merge($this->_fileRenders,
                require_once($this->_appDir . '/config/fileRenders.php'));
        }

        // 设置主题名称
        $this->_themeName = $this->_config['theme_name'];

        // 设置应用标题
        $this->title = $this->_config['title'];

        // 设置数据路径
        $this->_baseDir = isset($this->_config['base_dir']) ? $this->_config['base_dir'] : '.';
    }

     /**
     * If it is allowed to zip whole directories
     *
     * @param string $directory Relative path of directory to list
     * @return true or false
     * @access public
     */
    public function isZipEnabled() {
        foreach ($this->_config['zip_disable'] as $disabledPath) {
            if (fnmatch($disabledPath, $this->_directory)) {
                return false;
            }
        }
        return $this->_config['zip_dirs'];
    }

     /**
     * Creates zipfile of directory
     *
     * @param string $directory Relative path of directory to list
     * @access public
     */
    public function zipDirectory($directory) {

        if ($this->_config['zip_dirs']) {

            // Cleanup directory path
            $directory = $this->setDir($directory);

            if ($directory != '.' && $this->_isHidden($directory)) {
                echo "Access denied.";
            }

            $filename_no_ext = basename($directory);

            if ($directory == '.') {
                $filename_no_ext = 'Kaixinguo\'s files';
            }

            // We deliver a zip file
            header('Content-Type: archive/zip');

            // 浏览器的文件名保存zip文件
            header("Content-Disposition: attachment; filename=\"$filename_no_ext.zip\"");

            //change directory so the zip file doesnt have a tree structure in it.
            chdir($this->absPath($directory));

            // TODO: Probably we have to parse exclude list more carefully
            $exclude_list = implode(' ', array_merge($this->_config['hidden_files'], array('index.php')));
            $exclude_list = str_replace("*", "\*", $exclude_list);

            if ($this->_config['zip_stream']) {

                // zip the stuff (dir and all in there) into the streamed zip file
                $stream = popen('/usr/bin/zip -' . $this->_config['zip_compression_level'] . ' -r -q - * -x ' . $exclude_list, 'r');

                if ($stream) {
                   fpassthru($stream);
                   fclose($stream);
                }

            } else {

                // get a tmp name for the .zip
                $tmp_zip = tempnam('tmp', 'tempzip') . '.zip';

                // zip the stuff (dir and all in there) into the tmp_zip file
                exec('zip -' . $this->_config['zip_compression_level'] . ' -r ' . $tmp_zip . ' * -x ' . $exclude_list);

                // calc the length of the zip. it is needed for the progress bar of the browser
                $filesize = filesize($tmp_zip);
                header("Content-Length: $filesize");

                // deliver the zip file
                $fp = fopen($tmp_zip, 'r');
                echo fpassthru($fp);

                // clean up the tmp zip file
                unlink($tmp_zip);

            }
        }

    }


    /**
     * Creates the directory listing and returns the formatted XHTML
     *
     * @param string $directory Relative path of directory to list
     * @return array Array of directory being listed
     * @access public
     */
    public function listDirectory($directory, $file = null) {

        $directory = rawurldecode($directory);
        $file = rawurldecode($file);

        // Set directory
        $directory = $this->setDir($directory);

        // Set directory variable if left blank
        if ($directory === null) {
            $directory = $this->_directory;
        }

        if (!$this->_isAuthorized($directory)) {
            // Set the auth status
            $this->_authStatus = false;

            // Return empty array
            return [];
        }

        // Set file
        $file = $this->setFile($file);

        // Get the directory array
        $directoryArray = $this->_readDirectory($directory);

        $this->_dirArray = $directoryArray;

        // Return the array
        return $directoryArray;
    }


    /**
     * Parses and returns an array of breadcrumbs
     *
     * @param string $directory Path to be breadcrumbified
     * @return array Array of breadcrumbs
     * @access public
     */
    public function listBreadcrumbs($directory = null) {

        // Set directory variable if left blank
        if ($directory === null) {
            $directory = $this->_directory;
        }

        // Explode the path into an array
        $dirArray = explode('/', $directory);

        // 静态设置主页路径
        $breadcrumbsArray[] = array(
            'link' => $this->_appURL,
            'text' => $this->title
        );

        // Generate breadcrumbs
        foreach ($dirArray as $key => $dir) {

            if ($dir != '.') {

                $dirPath  = null;

                // 构建目录路径
                for ($i = 0; $i <= $key; $i++) {
                    $dirPath = $dirPath . $dirArray[$i] . '/';
                }

                // 删除尾部斜杠
                if(substr($dirPath, -1) == '/') {
                    $dirPath = substr($dirPath, 0, -1);
                }

                // 组合基本路径和dir路径
                $link = $this->_appURL . '?dir=' . rawurlencode($dirPath);

                $breadcrumbsArray[] = array(
                    'link' => $link,
                    'text' => $dir
                );

            }

        }

        // 返回breadcrumb数组
        return $breadcrumbsArray;
    }


    /**
     * Determines if a directory contains an index file and return it
     *
     * @param string $dirPath Path to directory to be checked for an index
     * @return string Returns name of index file
     * @access public
     */
    public function findIndexFile($dirPath) {

        // 检查目录是否包含索引文件
        foreach ($this->_config['index_files'] as $indexFile) {

            if (file_exists($this->absPath($dirPath) . '/' . $indexFile)) {

                return $indexFile;

            }

        }

        return null;

    }


    /**
     * Get URL
     *
     * @return string URL
     * @access public
     */
    public function getURL($dir, $file) {
        if (empty($file)) {
            return '?dir=' . rawurlencode($dir);
        } else {
            return '?dir=' . rawurlencode($dir) . '&file=' . rawurlencode($file);
        }
    }


    /**
     * Get App URL
     *
     * @return string App URL
     * @access public
     */
    public function getAppURL() {
        return $this->_appURL;
    }


    /**
     * Get Listed Dirs
     *
     * @return Array Listed Dirs
     * @access public
     */
    public function getDirArray() {
        return $this->_dirArray;
    }


    /**
     * Get path of the listed directory
     *
     * @return string Path of the listed directory
     * @access public
     */
    public function getDir() {

        // Return the path
        return $this->_directory;
    }


    /**
     * Get path of the displayed file
     *
     * @return string Path of the displayed file
     * @access public
     */
    public function getFile() {

        // Return the path
        return $this->_file;
    }


    /**
     * Returns the theme name.
     *
     * @return string Theme name
     * @access public
     */
    public function getThemeName() {
        // Return the theme name
        return $this->_config['theme_name'];
    }


    /**
     * Returns open links in another window
     *
     * @return boolean Returns true if in config is enabled open links in another window, false if not
     * @access public
     */
    public function externalLinksNewWindow() {
        return $this->_config['external_links_new_window'];
    }


    /**
     * Returns the path to the chosen theme directory
     *
     * @param bool $absolute Whether or not the path returned is absolute (default = false).
     * @return string Path to theme
     * @access public
     */
    public function getThemePath($absolute = false) {
        if ($absolute) {
            // Set the theme path
            $themePath = $this->_appDir . '/themes/' . $this->_themeName;
        } else {
            // Get relative path to application dir
            $realtivePath = $this->_getRelativePath(getcwd(), $this->_appDir);

            // Set the theme path
            $themePath = $realtivePath . '/themes/' . $this->_themeName;
        }

        return $themePath;
    }

    /**
     * Get auth status
     *
     * @return bool auth status
     * @access public
     */
    public function getAuthStatus() {
        if (!isset($this->_authStatus)) {
            return true;
        } else {
            return $this->_authStatus;
        }
    }

    /**
     * Get an array of error messages or false when empty
     *
     * @return array|bool Array of error messages or false
     * @access public
     */
    public function getSystemMessages() {
        if (isset($this->_systemMessage) && is_array($this->_systemMessage)) {
            return $this->_systemMessage;
        } else {
            return false;
        }
    }


    /**
     * Returns string of file size in human-readable format
     *
     * @param  string $filePath Path to file
     * @return string Human-readable file size
     * @access public
     */
    function getFileSize($filePath) {

        // 获取文件大小
        $bytes = filesize($filePath);

        // 文件大小后缀数组
        $sizes = array('B', 'KB', 'MB', 'GB', 'TB', 'PB');

        // 计算文件大小后缀系数
        $factor = floor((strlen($bytes) - 1) / 3);

        // 计算文件大小
        $fileSize = sprintf('%.2f', $bytes / pow(1024, $factor)) . $sizes[$factor];

        return $fileSize;

    }


    /**
     * Returns array of file hash values
     *
     * @param  string $filePath Path to file
     * @return array Array of file hashes
     * @access public
     */
    public function getFileHash($filePath) {

        // Placeholder array
        $hashArray = array();

        // Verify file path exists and is a directory
        if (!file_exists($filePath)) {
            return json_encode($hashArray);
        }

        // Prevent access to hidden files
        if ($this->_isHidden($filePath)) {
            return json_encode($hashArray);
        }

        // Prevent access to parent folders
        if (strpos($filePath, '<') !== false || strpos($filePath, '>') !== false
        || strpos($filePath, '..') !== false || strpos($filePath, '/') === 0) {
            return json_encode($hashArray);
        }

        // Prevent hashing if file is too big
        if (filesize($filePath) > $this->_config['hash_size_limit']) {

            // Notify user that file is too large
            $hashArray['md5']  = '[ 文件大小超过阈值 ]';
            $hashArray['sha1'] = '[ 文件大小超过阈值 ]';

        } else {

            // Generate file hashes
            $hashArray['md5']  = hash_file('md5', $filePath);
            $hashArray['sha1'] = hash_file('sha1', $filePath);

        }

        // Return the data
        return $hashArray;

    }


    /**
     * Set directory path variable
     *
     * @param string $path Path to directory
     * @return string Sanitizd path to directory
     * @access public
     */
    public function setDir($path = null) {

        // Set the directory global variable
        $this->_directory = $this->_setDir($path);

        return $this->_directory;

    }


    /**
     * Set file path variable
     *
     * @param string $path Path to file
     * @return string Sanitizd path to file
     * @access public
     */
    public function setFile($path = null) {

        if (empty($path)) {
            $this->_onlyRender = false;
            $this->_file = $this->findIndexFile($this->getDir());
        } else {
            $this->_onlyRender = true;
            $this->_file = $path;
        }

        return $this->_file;
    }


    /**
     * Add a message to the system message array
     *
     * @param string $type The type of message (ie - error, success, notice, etc.)
     * @param string $message The message to be displayed to the user
     * @return bool true on success
     * @access public
     */
    public function setSystemMessage($type, $text) {

        // Create empty message array if it doesn't already exist
        if (isset($this->_systemMessage) && !is_array($this->_systemMessage)) {
            $this->_systemMessage = array();
        }

        // Set the error message
        $this->_systemMessage[] = array(
            'type'  => $type,
            'text'  => $text
        );

        return true;
    }


    /**
     * Render file
     *
     * @param string $dir
     * @param string $file
     * @return string bsolute path of dir or file
     * @access public
     */
    public function renderFile() {
        // Get files absolute path
        $dir = $this->getDir();
        $file = $this->getFile();
        $path = $this->absPath($dir, $file);
        $dirArray = $this->getDirArray();

        if (empty($file)) {
            return;
        }

        // Is Directory
        if (is_dir($path)) {
            $dir = $dir . '/' . $file;
            $file = $this->findIndexFile($dir);
            $path = $this->absPath($dir, $file);

            if(substr($dir, 0, 2) == './') {
                $dir = substr($dir, 2);
            }

            header('Location:' . $this->getURL($dir, $file));
            return;
        }

        // Get file extension
        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));

        if (isset($this->_fileRenders[$ext])) {
            $render = $this->_fileRenders[$ext];
        } else {
            $render = $this->_fileRenders['blank'];
        }

        if (!empty($render)) {
            if (file_exists($path)) {
                $lister = $this;
                require_once($this->_appDir . '/lib/RenderUtils.php');
                require($this->_appDir . '/renders/' . $render . '/render.php');
            } else {
                echo "<div style='margin: 42px auto; width: fit-content; font-size: 20px;'>404 not found</div>";
            }
        } else {
            echo "<div style='margin: 42px auto; width: fit-content; font-size: 20px;'>Unsupported File</div>";
        }
    }


    /**
     * Get absolute path of dir or file
     *
     * @param string $dir
     * @param string $file
     * @return string bsolute path of dir or file
     * @access public
     */
    public function absPath($dir, $file = null) {
        if (empty($dir) || $dir == '.') {
            $path = $this->_baseDir;
        } else {
            $path = $this->_baseDir . '/' . $dir;
        }
        if (isset($file)) {
            return $path . '/' . $file;
        } else {
            return $path;
        }
    }

    /**
     * Validates and returns the directory path
     *
     * @param string $dir Directory path
     * @return string Directory path to be listed
     * @access protected
     */
    protected function _setDir($dir) {

        // Check for an empty variable
        if (empty($dir) || $dir == '.') {
            return '.';
        }

        // Eliminate double slashes
        while (strpos($dir, '//')) {
            $dir = str_replace('//', '/', $dir);
        }

        // Remove trailing slash if present
        if(substr($dir, -1, 1) == '/') {
            $dir = substr($dir, 0, -1);
        }

        $this->_directory = $dir;

        // Verify file path exists and is a directory
        if (!file_exists($this->absPath($dir)) || !is_dir($this->absPath($dir))) {
            // Set the error message
            $this->setSystemMessage('danger', '<b>ERROR:</b> 文件路径不存在');

            // Return the web root
            return '.';
        }

        // Prevent access to hidden files
        if ($this->_isHidden($dir)) {
            // Set the error message
            $this->setSystemMessage('danger', '<b>ERROR:</b> 拒绝访问');

            // Set the directory to web root
            return '.';
        }

        // Prevent access to parent folders
        if (strpos($dir, '<') !== false || strpos($dir, '>') !== false
        || strpos($dir, '..') !== false || strpos($dir, '/') === 0) {
            // Set the error message
            $this->setSystemMessage('danger', '<b>ERROR:</b> 检测到无效的路径字符串');

            // Set the directory to web root
            return '.';
        } else {
            // Should stop all URL wrappers (Thanks to Hexatex)
            $directoryPath = $dir;
        }

        // Return
        return $directoryPath;
    }


    /**
     * Loop through directory and return array with file info, including
     * file path, size, modification time, icon and sort order.
     *
     * @param string $directory Directory path
     * @param string $sort Sort method (default = natcase)
     * @return array Array of the directory contents
     * @access protected
     */
    protected function _readDirectory($directory, $sort = 'natcase') {

        // Initialize array
        $directoryArray = array();

        // Get directory contents
        $files = scandir($this->absPath($directory));

        // Read files/folders from the directory
        foreach ($files as $file) {

            if ($file == '.') {
                continue;
            }

            // Don't check parent dir if we're in the root dir
            if ($this->_directory == '.' && $file == '..'){
                continue;
            }

            if ($this->_directory == '.' && $file == 'index.php') {
                continue;
            }

            // Get files relative path
            $relativePath = $directory . '/' . $file;
            if (substr($relativePath, 0, 2) == './') {
                $relativePath = substr($relativePath, 2);
            }

            // Get files absolute path
            $realPath = realpath($this->absPath($relativePath));

            // Determine file type by extension
            if (is_dir($realPath)) {
                $iconClass = 'fa-folder';
                $sort = 1;
            } else {
                // Get file extension
                $fileExt = strtolower(pathinfo($realPath, PATHINFO_EXTENSION));

                if (isset($this->_fileTypes[$fileExt])) {
                    $iconClass = $this->_fileTypes[$fileExt];
                } else {
                    $iconClass = $this->_fileTypes['blank'];
                }

                if (isset($this->_fileRenders[$fileExt])) {
                    $render = $this->_fileRenders[$fileExt];
                } else {
                    $render = $this->_fileRenders['blank'];
                }

                $sort = 2;
            }

            if ($file == '..') {

                // Get parent directory path
                $pathArray = explode('/', $relativePath);
                unset($pathArray[count($pathArray)-1]);
                unset($pathArray[count($pathArray)-1]);
                $directoryPath = implode('/', $pathArray);

                if (!empty($directoryPath)) {
                    $urlPath = '?dir=' . rawurlencode($directoryPath);
                }

                // Add file info to the array
                $directoryArray['..'] = array(
                    'name'      => $file,
                    'file_path' => $directoryPath,
                    'url_path'  => $this->_appURL . $urlPath,
                    'size'      => '-',
                    'time'      => date('Y-m-d H:i:s', filemtime($realPath)),
                    'icon'      => 'fa-level-up',
                    'sort'      => 0
                );

            } elseif (!$this->_isHidden($relativePath)) {

                // Add all non-hidden files to the array
                if ($this->_directory != '.' || $file != 'index.php') {

                    // Build the file path
                    $urlPath = implode('/', array_map('rawurlencode', explode('/', $relativePath)));

                    if (is_dir($this->absPath($relativePath))) {
                        $urlPath = '?dir=' . rawurlencode($urlPath);
                        $indexFile = $this->findIndexFile($relativePath);
                        $renderable = !empty($indexFile);
                    } else {
                        $urlPath = $this->absPath($urlPath);
                        $renderable = !empty($render);
                    }

                    // Add the info to the main array by larry
                    preg_match('/\/([^\/]*)$/', $relativePath, $matches);
                    $pathname = isset($matches[1]) ? $matches[1] : $relativePath;
                    //$directoryArray[pathinfo($relativePath, PATHINFO_BASENAME)] = array(
                    $directoryArray[$pathname] = array(
                        'name'      => $file,
                        'file_path' => $relativePath,
                        'url_path'  => $urlPath,
                        'size'      => is_dir($realPath) ? '-' : $this->getFileSize($realPath),
                        'time'      => date('Y-m-d H:i:s', filemtime($realPath)),
                        'icon'      => $iconClass,
                        'renderable'=> $renderable,
                        'sort'      => $sort
                    );
                }

            }
        }

        // Sort the array
        if (file_exists($sortConfig = $this->absPath($directory) . '/.sort')) {
            $sortConfig = trim(file_get_contents($sortConfig));
        } else {
            $sortConfig = $this->_config['list_sort'];
        }
        $sortedArray = $this->_arraySort($directoryArray, $sortConfig);

        // Return the array
        return $sortedArray;

    }


    /**
     * Sorts an array by the provided sort method.
     *
     * @param array $array Array to be sorted
     * @param string $sortMethod Sorting method (acceptable inputs: natsort, natcasesort, etc.)
     * @param boolen $reverse Reverse the sorted array order if true (default = false)
     * @return array
     * @access protected
     */
    protected function _arraySort($array, $config = 'name asc') {
        $configs = explode(',', $config);
        $sort = Array();

        // 默认排列顺序
        foreach ($array as $k => $v) {
            $keys[$k] = $v['sort'];
        }
        $sort[] = $keys;
        $sort[] = SORT_ASC;

        // 自定义排列顺序
        foreach ($configs as $c) {
            $c = explode(' ', $c);
            foreach ($array as $k => $v) {
                $keys[$k] = $v[trim($c[0])];
            }
            $sort[] = $keys;
            $sort[] = (trim($c[1]) == 'desc') ? SORT_DESC : SORT_ASC;
        }

        $sort[] = &$array;
        array_multisort(...$sort);

        return $array;
    }


    /**
     * Determines if a directory is allowed to access
     *
     * @param string $filePath Path to file to be checked
     * @return boolean Returns true if file is allowed
     * @access protected
     */
    protected function _isAuthorized($filePath) {
        $path = $this->absPath($filePath) . '/.password';

        // 获取目标密码
        if (file_exists($path)) {
            $password = trim(file_get_contents($path));
        }
        if (empty($password)) {
            return true;
        }

        // 获取输入密码
        $id = "p:" . md5($filePath);
        if (isset($_POST['password'])) {
            $input = $_POST['password'];
        } else if (isset($_COOKIE[$id])) {
            $input = $_COOKIE[$id];
        }

        // 效验密码
        if ($input == $password) {
            setcookie($id, $input);
            return true;
        } else {
            setcookie($id, "", time()-3600);
            return false;
        }

        return false;
    }

    /**
     * Determines if a file is specified as hidden
     *
     * @param string $filePath Path to file to be checked if hidden
     * @return boolean Returns true if file is in hidden array, false if not
     * @access protected
     */
    protected function _isHidden($filePath) {

        // Add dot files to hidden files array
        if ($this->_config['hide_dot_files']) {

            $this->_config['hidden_files'] = array_merge(
                $this->_config['hidden_files'],
                array('.*', '*/.*')
            );

        }

        // Compare path array to all hidden file paths
        foreach ($this->_config['hidden_files'] as $hiddenPath) {

            if (fnmatch($hiddenPath, $filePath)) {

                return true;

            }

        }

        return false;

    }


    /**
     * Builds the root application URL from server variables.
     *
     * @return string The application URL
     * @access protected
     */
    protected function _getAppUrl() {

        // Get the server protocol
        if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
            $protocol = 'https://';
        } else {
            $protocol = 'http://';
        }

        // Get the server hostname
        $host = $_SERVER['HTTP_HOST'];

        // Get the URL path
        $pathParts = pathinfo($_SERVER['PHP_SELF']);
        $path      = $pathParts['dirname'];

        // Remove backslash from path (Windows fix)
        if (substr($path, -1) == '\\') {
            $path = substr($path, 0, -1);
        }

        // Ensure the path ends with a forward slash
        if (substr($path, -1) != '/') {
            $path = $path . '/';
        }

        // Build the application URL
        $appUrl = $protocol . $host . $path;

        // Return the URL
        return $appUrl;
    }


    /**
      * Compares two paths and returns the relative path from one to the other
     *
     * @param string $fromPath Starting path
     * @param string $toPath Ending path
     * @return string $relativePath Relative path from $fromPath to $toPath
     * @access protected
     */
    protected function _getRelativePath($fromPath, $toPath) {

        // Define the OS specific directory separator
        if (!defined('DS')) define('DS', DIRECTORY_SEPARATOR);

        // Remove double slashes from path strings
        $fromPath = str_replace(DS . DS, DS, $fromPath);
        $toPath = str_replace(DS . DS, DS, $toPath);

        // Explode working dir and cache dir into arrays
        $fromPathArray = explode(DS, $fromPath);
        $toPathArray = explode(DS, $toPath);

        // Remove last fromPath array element if it's empty
        $x = count($fromPathArray) - 1;

        if(!trim($fromPathArray[$x])) {
            array_pop($fromPathArray);
        }

        // Remove last toPath array element if it's empty
        $x = count($toPathArray) - 1;

        if(!trim($toPathArray[$x])) {
            array_pop($toPathArray);
        }

        // Get largest array count
        $arrayMax = max(count($fromPathArray), count($toPathArray));

        // Set some default variables
        $diffArray = array();
        $samePath = true;
        $key = 1;

        // Generate array of the path differences
        while ($key <= $arrayMax) {

            // Get to path variable
            $toPath = isset($toPathArray[$key]) ? $toPathArray[$key] : null;

            // Get from path variable
            $fromPath = isset($fromPathArray[$key]) ? $fromPathArray[$key] : null;

            if ($toPath !== $fromPath || $samePath !== true) {

                // Prepend '..' for every level up that must be traversed
                if (isset($fromPathArray[$key])) {
                    array_unshift($diffArray, '..');
                }

                // Append directory name for every directory that must be traversed
                if (isset($toPathArray[$key])) {
                    $diffArray[] = $toPathArray[$key];
                }

                // Directory paths have diverged
                $samePath = false;
            }

            // Increment key
            $key++;
        }

        // Set the relative thumbnail directory path
        $relativePath = implode('/', $diffArray);

        // Return the relative path
        return $relativePath;

    }

}
