<?php
include_once( 'config.php' );
include_once( 'saetv2.ex.class.php' );

$o = new SaeTOAuthV2( WB_AKEY , WB_SKEY );

if (isset($_REQUEST['code'])) {
	$keys = array();
	$keys['code'] = $_REQUEST['code'];
	$keys['redirect_uri'] = WB_CALLBACK_URL;
	try {
		$token = $o->getAccessToken( 'code', $keys );
	} catch (OAuthException $e) {
	}
}

if ($token) {
	$weibo = new SaeTClientV2( WB_AKEY , WB_SKEY , $token['access_token'] );
	$userInfo = $weibo->show_user_by_id($token['uid']);
	if(isset($userInfo['name']))
	{
		$token['name'] = $userInfo['name'];
		file_put_contents(OAUTH_FILE, serialize($token));
	}
	else
		echo '获取用户名称失败'.PHP_EOL;
?>
授权完成
<?php
} else {
?>
授权失败。
<?php
}
?>
