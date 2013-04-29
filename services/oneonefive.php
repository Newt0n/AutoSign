<?php
/**
 * 115签到
 * @author Newton <mciguu@gmail.com>
 */
class OneOneFive extends Sign
{
	//唯一实例静态变量
	protected static $_instance = NULL;

	//服务名称前缀
	protected $preFix = '115_';

	//cookie 存在标识
	protected $isCookieExist = true;

	//登录 URL
	private $homeUrl = 'http://115.com/';
	private $postUrl = 'http://passport.115.com/?ct=login&ac=ajax&is_ssl=1';

	//签到 URL
	private $signUrl = 'http://115.com/?ct=ajax_user&ac=pick_spaces&u=1&token=';

	/**
	 * 签到方法
	 */
	public function sign()
	{
		if(!$this->isCookieExist)
		{
			$vcode = uniqid();
			$data = array(
				'login[ssoent]'=>'A1',
				'login[version]'=>'2.0',
				'login[ssoext]'=>$vcode,
				'login[ssoln]'=>$this->username,
				'login[ssopw]'=>$this->sha1_pwd($this->password, $this->username, $vcode),
				'login[ssovcode]'=>$vcode,
				'login[time]'=>1,
				'goto'=>''
				);
			$httpheader = array(
				'X-Requested-With:XMLHttpRequest'
				);
			$this->post($this->postUrl, $data, $httpheader);
		}

		$homeResp = $this->get($this->homeUrl);
		preg_match('/take_token:\s*\'([^\']*)/', $homeResp, $match);
		if(empty($match[0]))
			$this->retry();
		if(empty($match[1]))
		{
			$this->logLine .= self::SIGNED;
			return;
		}

		$token = $match[1];
		$signResp = $this->get($this->signUrl.$token.'&_='.time());
		$signResp = json_decode($signResp);
		if(isset($signResp->picked))
			$this->logLine .= self::SUCCESS.' 获得空间：'.$signResp->picked.' 总容量：'.$signResp->total_size;
		else
		{
			@$this->logLine .= $signResp->msg;
			$this->retry();
		}
	}

	private function sha1_pwd($password, $username, $vcode)
	{
		return sha1(sha1(sha1($password).sha1($username)).strtoupper($vcode));
	}
}

?>