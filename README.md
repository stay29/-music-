# erp

#### 介绍
琴行管理

#### 软件架构
- 框架thinkphp5.1 + layui

- 目录结构
```
├── application
│   ├── admin   # 后台模块
│   ├── command.php
│   ├── common.php
│   ├── index   # 前台接口模块
│   ├── provider.php
│   ├── tags.php
│   └── Validate
├── build.php
├── composer.json  # composer依赖文件
├── composer.lock
├── config  # 配置文件
│   ├── app.php
│   ├── cache.php
│   ├── console.php
│   ├── cookie.php
│   ├── database.php
│   ├── log.php
│   ├── middleware.php
│   ├── session.php
│   ├── template.php
│   └── trace.php
├── extend
├── public
│   ├── aboutus.json
│   ├── debug.txt
│   ├── favicon.ico
│   ├── index.php
│   ├── robots.txt
│   ├── router.php
│   ├── static
│   └── web.config
├── README.md
├── route
│   └── route.php
├── sftp-config.json
```

#### 使用说明

1. `git clone git@gitee.com:zhouzhihao/erp.git`
2. `cd erp`
3. `composer install`
4. `windows需要修改public/.htaccess。`

#### 开发规范
##### 目录和文件
- 目录使用小写+下划线；
- 类库、函数文件统一以.php为后缀；
- 类的文件名均以命名空间定义，并且命名空间的路径和类库文件所在路径一致；
- 类文件采用驼峰法命名（首字母大写），其它文件采用小写+下划线命名；
- 类名和类文件名保持一致，统一采用驼峰法命名（首字母大写）；
##### 函数和类、属性命名
- 类的命名采用驼峰法（首字母大写），例如 User、UserType，默认不需要添加后缀，例如UserController应该直接命名为User；
- 函数的命名使用小写字母和下划线（小写字母开头）的方式，例如 get_client_ip；
- 方法的命名使用驼峰法（首字母小写），例如 getUserName；
- 属性的命名使用驼峰法（首字母小写），例如 tableName、instance；
特例：以双下划线__打头的函数或方法作为魔术方法，例如 __call 和 __autoload；
##### 常量和配置
- 常量以大写字母和下划线命名，例如 APP_PATH；
- 配置参数以小写字母和下划线命名，例如 url_route_on 和url_convert；
- 环境变量定义使用大写字母和下划线命名，例如APP_DEBUG；
##### 数据表和字段
- 数据表和字段采用小写加下划线方式命名，并注意字段名不要以下划线开头，例如 think_user 表和 user_name字段，不建议使用驼峰和中文作为数据表及字段命名。
