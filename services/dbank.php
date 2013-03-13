<?php
/**
 * DBank 签到
 * @author Newton <mciguu@gmail.com>
 */
class DBank extends Sign
{
	//唯一实例静态变量
	protected static $_instance = NULL;

	//服务名称前缀
	protected $preFix = 'DBank_';

	//cookie 存在标识
	protected $isCookieExist = true;
	protected $sercookieFile;

	private $homeUrl = 'http://www.dbank.com/';
	//登录 URL
	private $loginUrl = 'http://login.dbank.com/loginauth.php?';

	//签到 URL/参数
	private $signUrl = 'http://api.dbank.com/rest.php?';
	private $signParams = array(
		// 'anticache'=>969,
		// 'nsp_cb'=>'_jqjsp',
		'nsp_fmt'=>'JS', //响应格式 JSON
		'nsp_sid'=>'', //session 值
		'nsp_svc'=>'com.dbank.signin.signin', //服务类型标识
		'nsp_ts'=>'' //时间戳
		);

	//hao123 转发多得 100M 参数
	private $hao123Svc = array(
		'nsp_svc'=>'com.dbank.signin.forwordsign',
		'signtype'=>7
		);

	/**
	 * 签到方法
	 */
	public function sign()
	{
		//序列化 cookie 文件路径
		$this->sercookieFile = $this->cookieDir.$this->cookieName.'.sercookie';

		//序列化文件存在直接读取，否则重新登录
		if(file_exists($this->sercookieFile))
		{
			$sercookie = file_get_contents($this->sercookieFile);
			$cookies   = unserialize($sercookie);
		}
		else
		{
			//登录 POST 数据
			$loginData = array(
				'nsp_user'=>$this->username,
				'm'=>1,
				'nonce'=>'5125bf611d26b9.03022678',
				);
			$loginData['response'] = md5($loginData['nsp_user'].':NSP Passport:'.$this->password);
			$loginData['response'] = md5($loginData['response'].':'.$loginData['nonce']);

			//HTTP 头
			$httpheader = array(
				'Origin: http://login.dbank.com',
				'Referer: http://login.dbank.com/loginauth.php?nsp_app=48049',
				'X-Requested-With:XMLHttpRequest'
				);

			//获取 k 值
			$kUrl  = $this->loginUrl.'nsp_app=48049';
			$kResp = $this->post($kUrl, http_build_query($loginData), $httpheader);
			$kResp = json_decode($kResp);
			if(!isset($kResp->k))
				$this->retry(0);
			
			//访问验证地址，获得 cookie
			$authUrl = $this->loginUrl.'k='.$kResp->k;
			$header = $this->get($authUrl, true, true);
			preg_match_all('/Set-Cookie: (.+?)=(.+?);/', $header, $match);
			$cookies = array();
			foreach ($match[1] as $key => $value)
				$cookies[$value] = $match[2][$key];

			//序列化 cookie 值
			file_put_contents($this->sercookieFile, serialize($cookies));
		}

		//签到参数
		$signParams = $this->signParams;
		$signParams['nsp_sid'] = $cookies['session'];
		$signParams['nsp_ts'] = $_SERVER['REQUEST_TIME'];
		//签到
		$this->signThis($signParams, $cookies['secret']);

		//hao123 转发参数
		$hao123Params = array_merge($signParams, $this->hao123Svc);
		//hao123 转发
		$this->signThis($hao123Params, $cookies['secret']);

	}

	/**
	 * 根据参数生成链接并请求
	 * @param  array  $signParams 请求参数
	 * @param  string $secretKey  cookie 中 secretKey 值
	 */
	private function signThis($signParams, $secretKey)
	{
		//计算 nsp_key
		foreach ($signParams as $key => $value)
			$secretKey .= $key.$value;
		$signParams['nsp_key'] = md5($secretKey);
		
		//生成签到链接
		$signUrl = $this->signUrl.http_build_query($signParams);

		//执行请求
		$signResp = $this->get($signUrl);
		$signResp = json_decode($signResp);

		//处理请求结果
		if(isset($signResp->retdesc))	
			$this->logLine .= $signResp->retdesc.' ';
		else
			$this->retry(1, $this->sercookieFile);
	}
}

?>