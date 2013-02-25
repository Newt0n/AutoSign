AutoSign
========

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