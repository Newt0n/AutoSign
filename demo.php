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
$accounts = array(
	array('KuaiPan', 'Your username', 'Your password'),
	array('XiaMi', 'Your username', 'Your password'),
	array('OneOneFive', 'Your username', 'Your password'),
	array('YinYueTai', 'Your username', 'Your password'),
	array('DBank', 'Your username', 'Your password'),
	array('Vdisk', 'Your username', 'Your password')
	);

//config.inc.php 配置选项
$executor = new Executor($accounts);

?>