<?php
/**
 * 快盘签到
 * @author Newton <mciguu@gmail.com>
 */
class KuaiPan extends Sign
{
	//唯一实例静态变量
	protected static $_instance = NULL;

	//服务名称前缀
	protected $preFix = 'KuaiPan_';

	//cookie 存在标识
	protected $isCookieExist = true;

	//登录 URL
	private $loginUrl = 'http://www.kuaipan.cn/home.htm';
	private $postUrl = 'https://www.kuaipan.cn/index.php?ac=account&op=login';

	//签到 URL
	private $signUrl = 'http://www.kuaipan.cn/index.php?ac=common&op=usersign';

	/**
	 * 签到方法
	 */
	public function sign()
	{
		if(!$this->isCookieExist)
		{
			$this->get($this->loginUrl);//获取 cookie
			$data = array(
				'username'=>$this->username,
				'userpwd'=>$this->password,
				'isajax'=>'yes',
				'rememberme'=>1
				);

			$loginResp = $this->post($this->postUrl, $data);
			$loginResp = json_decode($loginResp);
			if(!isset($loginResp->state) || $loginResp->state != '1')
				$this->retry(0);
		}
		else
			$this->get($this->loginUrl);

		$signResp = $this->get($this->signUrl);
		$signResp = json_decode($signResp);
		$state = null;
		if(isset($signResp->state))
			$state = $signResp->state;

		switch ($state)
		{
			case '-102':
				$this->logLine .= self::SIGNED;
			break;
			case '1':
				$this->logLine .= self::SUCCESS.' 获得空间：'.$signResp->rewardsize.'M';
			break;
			default:
				$this->retry();
			break;
		}
	}
}

?>