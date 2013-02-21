<!doctype html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title>AutoSign Demo</title>
</head>
<body>
</body>
</html>

<?php
require('executor.php');

//签到账户信息数组
$config = array(
	array('KuaiPan', 'Your username', 'Your password'),
	array('XiaMi', 'Your username', 'Your password'),
	array('OneOneFive', 'Your username', 'Your password'),
	array('YinYueTai', 'Your username', 'Your password'),
	);

$executor = new Executor($config);
//加参数 $log = false 不输出日志文件
//$executor = new Executor($config, false);

?>