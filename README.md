# Directory Lister 魔改版

### 魔改特性：

在逗比魔改版的基础上继续魔改而来，继续增加了一些新功能。

- 新增README.md自述文件展示
- 新增自定义根目录设置 (配置项config['base_dir'])
- 新增文件夹简单密码保护 (明文密码，低安全性)
- 等等 ...

### 下载安装：

下载压缩文件后，解压并上传到已经搭建好 PHP和HTTP环境的服务器中，然后即可上传文件和创建文件夹了！

#### 文件结构
假设你的虚拟主机是 `/home/wwwroot/xxx.xx`
``` bash
/home/wwwroot/xxx.xx
├─ resources
│   ├ themes
│   │ └ bootstrap
│   │    └ .....
│   │
│   ├ DirectoryLister.php
│   ├ config.php # 配置文件 #
│   └ fileTypes.php
│
├ README.md # 文件夹内的 说明简介文件 #
├ index.php
│
├─ your_data_dir # 可设置以子目录为公开展示的根目录 #
│   ├ 测试文件.txt
│   └ README.md # 文件夹内的 说明简介文件 #
│
├─ soft_link -> /mnt/media # 可使用软连接展示根目录外的文件 #
│
└ 测试文件.txt
```

### 新增特性介绍：

#### 简单密码保护

在文件夹下创建存有明文密码的` .password `文件，则开启该目录的密码保护，第一次浏览此目录时需输入密码（灵感来源：OneIndex）。

当前还有很多问题：
- 密码明文储存，明文传输
- 仅保护此目录，无法保护子目录
- 仅禁止浏览目录，无法阻止通过URL直接下载文件

**为防止未经授权用户通过构造URL直接下载.password文件导致密码泄露，需要在Nginx或Apache中禁止所有对.password文件的访问。**

#### 自定义根目录

有时不想使用网站根目录作为展示的根目录，这时可以通过配置项config['base_dir']自定义一个子目录为公开展示的根目录。

``` php
...
    'base_dir' => './your_data_dir'
...
```

#### 自述文件展示

每个文件夹下面的` README.md `文件会被渲染展示在文件列表下方，渲染使用的第三方库：Parsedown.php。

#### 网站修改说明

自定义网站标题：通过新增的配置项config['title']，在config.php中设置即可

自定义网站头部/底部： 在`/resources/themes/bootstrap/config`目录下创建`header.php`或`footer.php`。 

如果想要插入流量统计代码，那只需要把代码写到 header.php 文件内即可。

### 常见问题：

常见问题以及注意事项请参阅[原版文档](https://github.com/ToyoDAdoubi/DirectoryLister)。

本程序由[Directory Lister 逗比魔改版](https://github.com/ToyoDAdoubi/DirectoryLister)继续魔改而来。
- 原版官方网站：http://www.directorylister.com
- 魔改版Github：https://github.com/ToyoDAdoubi/DirectoryLister
