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
	array('OneOneFive', 'Your username', 'Your password')
	//设置 log 为 false 不输出日志
	// 'log'=>false
	);

$executor = new Executor($config);

?>