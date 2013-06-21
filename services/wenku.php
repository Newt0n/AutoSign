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

	//相关 URL
	private $homeUrl = 'http://wenku.baidu.com/user/task';
	private $postUrl = 'https://passport.baidu.com/v2/api/?login';
	private $getApiUrl = 'http://passport.baidu.com/v2/api/?getapi&class=login&tpl=do&tangram=true';
	private $awardDailyUrl = 'http://wenku.baidu.com/taskui/control/awarddailytask';
	private $wapUrl = 'http://wapwenku.baidu.com/uc/';
	private $loginNotiUrl = 'http://wenku.baidu.com/login';

	/**
	 * 设置 UA 为百度浏览器增加积分
	 */
	public function __construct()
	{
		$this->curl_opts[CURLOPT_USERAGENT] = 'Mozilla/5.0 (compatible; MSIE 10.0; Windows NT 6.1; Trident/6.0; BIDUBrowser 2.x)';
	}

	/**
	 * 签到方法
	 */
	public function sign()
	{
		$homeResp = $this->get($this->homeUrl);
		if(strpos($homeResp, '<li id="mywenku"') === false)
		{
			//获取 token
			$getResp = $this->get($this->getApiUrl);
			preg_match('/bdPass.api.params.login_token=\'([^\']*)\'/', $getResp, $match);
			if(empty($match[0]))
				$this->retry(2, '未获取到 token；');
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

		$httpheader = array(
			'Referer: '.$this->wapUrl
			);
		$this->get($this->wapUrl, $httpheader);
		//登录通知
		$this->get($this->loginNotiUrl, $httpheader);

		//连续签到奖励
		$this->awardDaily(4);
		$this->awardDaily(7);
		$this->logLine .= self::SIGNED;
	}

	private function awardDaily($days)
	{
		$data = array(
			'type'=>'task',
			'task_id'=>2,
			'prize'=>$days
			);

		$httpheader = array(
			'Origin:http://wenku.baidu.com',
			'Referer:http://wenku.baidu.com/task/browse/daily?tab=1',
			'X-Requested-With:XMLHttpRequest'
			);
		
		$resp = $this->post($this->awardDailyUrl, http_build_query($data), $httpheader);
		$resp = json_decode($resp);
		if(isset($resp->error_no) && $resp->error_no == 0)
			$this->logLine .= '领取签到 '.$days.' 天奖励 ';
	}

}

?>