<?php
/**
 * 微盘签到
 * @author Newton <mciguu@gmail.com>
 */
class Vdisk extends Sign
{
	//唯一实例静态变量
	protected static $_instance = NULL;

	//服务名称前缀
	protected $preFix = 'Vdisk_';

	//cookie 存在标识
	protected $isCookieExist = true;

	//登录 URL
	private $loginUrl = 'http://vdisk.weibo.com/wap/account/login';
	private $postUrl = 'https://login.weibo.cn/login/';
	private $signinfoUrl = "http://vdisk.weibo.com/wap/api/weipan/checkin/checkin_info";
	private $signUrl = 'http://vdisk.weibo.com/wap/api/weipan/checkin/checkin';
	private $ssoUrl = '';

	/**
	 * 签到方法
	 */
	public function sign()
	{
		if(!$this->isCookieExist)
		{
			$getResp = $this->get($this->loginUrl);
			
			//获取表单字段
			preg_match('/<form action="([^\"]*)"/', $getResp, $actionMatch);
			preg_match('/<input type="password" name="(password_\d*)"/', $getResp, $passMatch);
			preg_match('/<input type="hidden" name="vk" value="([^"]*)"/', $getResp, $vkMatch);
			preg_match('/<input type="hidden" name="backURL" value="([^"]*)"/', $getResp, $backURLMatch);
			if(empty($actionMatch[0]) || empty($passMatch[0]) || empty($vkMatch))
				throw new Exception("Login error", 0);

			//拼接登录数据 POST 地址
			$postUrl = $this->postUrl.$actionMatch[1];
			$data = array(
				'mobile'=>$this->username,
				$passMatch[1]=>$this->password,
				'remember'=>'',
				'tryCount'=>'',
				'backURL'=>$backURLMatch[1],
				'backTitle'=>'手机新浪网',
				'remember'=>'on',
				'vk'=>$vkMatch[1],
				'submit'=>'登录'
				);
			$loginResp = $this->post($postUrl, $data);
			//获取sso登录地址
			preg_match('/<a href="([^"]*)">/', $loginResp, $match);
			$this->ssoUrl = $match[1];
			$this->get($this->ssoUrl);
		}

		$httpheader = array(
			'Referer: http://vdisk.weibo.com/wap/'
			);
		//查询签到数据
		$signInfoResp = $this->get($this->signinfoUrl, $httpheader);
		if(empty($signInfoResp))
			$this->retry();

		if($signInfoResp === 'false')
		{
			//未签到则执行签到
			$signResp = $this->get($this->signUrl, $httpheader);
			$signResp = trim($signResp, '[]');
			$signResp = explode(',', $signResp);
			$this->logLine .= self::SUCCESS.'，获得空间 '.$signResp[0].'M';
		}
		else
			//已签到
			$this->logLine .= self::SIGNED;
	}
}

?>