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

		if($this->isCookieExist)
			$this->GETRequest($this->homeUrl, true);
		else
		{
			$this->GETRequest($this->homeUrl);

			$data = array(
				'email'=>$this->username,
				'password'=>$this->password,
				);
			$loginResult = $this->POSTRequest($this->loginUrl, http_build_query($data), $header);
			$loginResponse = json_decode($loginResult);

			if(!isset($loginResponse->error) || $loginResponse->error)
			{
				$this->logString .= self::LOGINFAILED;
				$this->retry();
			}
		}

		//签到
		$signResponse = $this->POSTRequest($this->signUrl, '', $header);
		$signResponse = json_decode($signResponse);

		//返回结果处理
		if(isset($signResponse->error))
		{
			$this->logString .= $signResponse->message;
			if(!$signResponse->error)
				$this->logString .= ' 已连续签到 '.$signResponse->signIn->continuousDays.' 天';
		}
		else
			$this->retry();
	}
}

?>