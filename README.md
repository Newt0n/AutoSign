AutoSign
=======

对某些有签到设定的 Web 服务自动签到

### Requirements
* PHP Version >= 5.3

### Support
* KuaiPan 快盘
* Xiami 虾米网
* OneOneFive 115
* YinYueTai 音悦台
* DBank 华为网盘

Example
======

```php
require('executor.php');

//签到账户信息数组
$accounts = array(
	array('KuaiPan', 'Your username', 'Your password'),
	array('XiaMi', 'Your username', 'Your password'),
	array('OneOneFive', 'Your username', 'Your password'),
	array('YinYueTai', 'Your username', 'Your password'),
	array('DBank', 'Your username', 'Your password'),
	);

//执行签到
$executor = new Executor($accounts);
```

Configure
======
### config.inc.php

```php
//是否输出日志
define('LOG', TRUE);
//允许的重试次数
define('RETRY_LIMIT', 3);
//失败超过上限微博通知
define('NOTIFY', FALSE);
//接收通知微博 ID
define('WEIBO_NAME', '');
```
### Weibo Notification*
当执行失败次数达到允许重试的上限后，通过新浪微博 @ 设定的用户名实现通知，基于新浪微博官方 SDK，配置步骤如下：

1. config.inc.php 中配置接收通知的微博用户 ID
2. 用另一个微博账户(不同于接收通知的账户)在新浪微博开放平台申请一个应用
3. 在 weibo/config.php 中配置申请的应用信息
4. 访问 http://wwwroot/autosign/weibo/ 用注册应用的账户完成授权
5. 完成

\* 此项默认关闭

\*删除 protected/weibo.token 再次执行 Step 4 可重新授权