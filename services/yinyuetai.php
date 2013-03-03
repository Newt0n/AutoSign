<?php
/**
 * 音悦台签到
 * @author Newton <mciguu@gmail.com>
 */
class YinYueTai extends Sign
{
	//唯一实例静态变量
	protected static $_instance = NULL;

	//服务名称前缀
	protected $preFix = 'YinYueTai_';

	//cookie 存在标识
	protected $isCookieExist = true;

	//登录 URL
	private $homeUrl  = 'http://www.yinyuetai.com/';
	private $loginUrl = 'http://www.yinyuetai.com/login-ajax';

	//签到 URL
	private $signUrl = 'http://i.yinyuetai.com/i/sign-in';

	/**
	 * 签到方法
	 */
	public function sign()
	{
		//设置 header
		$header = array(
			'Accept:application/json',
			'X-Request:JSON',
			'X-Requested-With: XMLHttpRequest'
			);

		if(!$this->isCookieExist)
		{
			$this->get($this->homeUrl);

			$data = array(
				'email'=>$this->username,
				'password'=>$this->password,
				);
			$loginResp = $this->post($this->loginUrl, http_build_query($data), $header);
			$loginResp = json_decode($loginResp);

			if(!isset($loginResp->error) || $loginResp->error)
			{
				$this->logLine .= self::LOGINFAILED;
				$this->retry();
			}
		}
		else
			$this->get($this->homeUrl);

		//签到
		$signResp = $this->post($this->signUrl, '', $header);
		$signResp = json_decode($signResp);

		//返回结果处理
		if(isset($signResp->error))
		{
			$this->logLine .= $signResp->message;
			if(!$signResp->error)
				$this->logLine .= ' 已连续签到 '.$signResp->signIn->continuousDays.' 天';
		}
		else
			$this->retry();
	}
}

?>