<?php
/**
 * 百度文库签到
 * @author Newton <mciguu@gmail.com>
 */
class WenKu extends Sign
{
	//唯一实例静态变量
	protected static $_instance = NULL;

	//服务名称前缀
	protected $preFix = 'WenKu_';

	//cookie 存在标识
	protected $isCookieExist = true;

	//登录 URL
	private $homeUrl = 'http://wenku.baidu.com/';
	private $postUrl = 'https://passport.baidu.com/v2/api/?login';
	private $getApiUrl = 'http://passport.baidu.com/v2/api/?getapi&class=login&tpl=do&tangram=true';

	/**
	 * 签到方法
	 */
	public function sign()
	{
		$homeResp = $this->get($this->homeUrl);
		//百度文库登录即签到
		if(strpos($homeResp, '<li id="mywenku"') === false)
		{
			//获取 token
			$getResp = $this->get($this->getApiUrl);
			preg_match('/bdPass.api.params.login_token=\'([^\']*)\'/', $getResp, $match);
			if(empty($match[0]))
				throw new Exception("", 0);
				// $this->retry(2, '未获取到 token；');
			$token = $match[1];

			//登录
			$data = array(
				// 'ppui_logintime'=>'',
				'charset' =>'utf-8',
				// 'codeString'=>'',
				'token'   =>$token,
				'isPhone' =>'false',
				'index'   =>'0',
				// 'u'=>'',
    			// 'safeflg'=>'0',
             	'staticpage'=>'http://www.baidu.com/cache/user/html/jump.html',
				'loginType' =>'1',
				'tpl'       =>'do',
				'callback'  =>"parent.bdPass.api.login._postCallback",
				'username'  =>$this->username,
				'password'  =>$this->password,
            	// 'verifycode'=>'',
            	'mem_pass'  =>'on'
				);

			$loginResp = $this->post($this->postUrl, http_build_query($data));
			if(strpos($loginResp, 'error=0') === false)
				$this->retry(0);
		}
		$this->logLine .= self::SIGNED;
	}
}

?>