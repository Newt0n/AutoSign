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
			$this->GETRequest($this->loginUrl);
			$data = array(
				'username'=>$this->username,
				'userpwd'=>$this->password,
				'isajax'=>'yes'
				);

			$loginResp = $this->POSTRequest($this->postUrl, $data);
			$loginResp = json_decode($loginResp);
			if(!isset($loginResp->state) || $loginResp->state != '1')
			{
				$this->logString .= self::LOGINFAILED;
				throw new Exception('Login failed', 0);
				
			}
		}
		else
			$this->GETRequest($this->loginUrl, true);

		$signResp = $this->GETRequest($this->signUrl, true);
		$signResp = json_decode($signResp);
		$state = null;
		if(isset($signResp->state))
			$state = $signResp->state;

		switch ($state)
		{
			case '-102':
				$this->logString .= self::SIGNED;
			break;
			case '1':
				$this->logString .= self::SUCCESS.' 获得空间：'.$signResp->rewardsize.'M';
			break;
			default:
				$this->retry();
			break;
		}			
	}
}

?>