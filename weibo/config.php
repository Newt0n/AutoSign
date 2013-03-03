<?php
header('Content-Type: text/html; charset=UTF-8');

//参考新浪微博开放平台说明填写
define( "WB_AKEY" , 'Your App key' );
define( "WB_SKEY" , 'Your App Secret' );
define( "WB_CALLBACK_URL" , 'Your callback URL' );

//授权 token 文件位置
define( "OAUTH_FILE", dirname(dirname(__FILE__)).'/protected/weibo.token');
//接收通知微博 ID
define('WEIBO_NAME', 'Newton');