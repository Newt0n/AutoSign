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
	private $loginUrl = 'http://115.com';
	private $postUrl = 'https://passport.115.com/?ac=login&goto=http%3A%2F%2Fwww.115.com';

	//签到 URL
	private $signUrl = 'http://115.com/?ct=ajax_user&ac=pick_space&token=';

	/**
	 * 签到方法
	 */
	public function sign()
	{
		if(!$this->isCookieExist)
		{
			$this->get($this->loginUrl);
			$data = array(
				'login[account]'=>$this->username,
				'login[passwd]'=>$this->password,
				'login[time]'=>'on',
				'back'=>'http://www.115.com'
				);
			$loginResp = $this->POSTRequest($this->postUrl, $data);
		}

		$getResp = $this->get($this->loginUrl);
		preg_match('/take_token:\s*\'([^\']*)/', $getResp, $match);
		if(empty($match[0]))
			$this->retry();
		if(empty($match[1]))
		{
			$this->logLine .= self::SIGNED;
			return;
		}

		$token = $match[1];
		$signResp = $this->get($this->signUrl.$token);
		$signResp = json_decode($signResp);
		if(isset($signResp->state))
			$this->logLine .= self::SUCCESS.' 获得空间：'.$signResp->picked.' 总容量：'.$signResp->total_size;
		else
			$this->retry();
	}
}

?>