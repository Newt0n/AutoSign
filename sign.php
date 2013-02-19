<?php
/**
 * 签到服务基类
 * @author Newton <mciguu@gmail.com>
 */
class Sign
{
	//日志状态
	const SUCCESS = '签到成功';
	const FAILED = '签到失败';
	const SIGNED = '今天已签到';

	//用户名和密码
	protected $username;
	protected $password;

	//cookie 文件名
	protected $cookieFile;

	//输出日志内容
	protected $logString;

	//CURL 选项
	protected $curl_opts = array(
			// CURLOPT_HEADER=>true,
			CURLOPT_RETURNTRANSFER =>true,
			CURLINFO_HEADER_OUT=>true,
			CURLOPT_USERAGENT =>'Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.22 (KHTML, like Gecko) Chrome/25.0.1364.58 Safari/537.22'
			);

	/**
	 * 私有构造函数
	 */
	private function __construct()
	{

	}

	/**
	 * 初始化配置信息
	 * @param  string $username
	 * @param  string $password
	 * @return void          
	 */
	public function init($username, $password)
	{
		$this->username = $username;
		$this->password = $password;
		$this->cookieFile = dirname(__FILE__).'/cookie/'.$this->preFix.$this->username.'.cookie';
		// echo $this->cookieFile.PHP_EOL;
		if(!file_exists($this->cookieFile))
		{
			fopen($this->cookieFile, 'w+');
			$this->isCookieExist = false;
		}
		$this->logString = $this->preFix.$this->username.' ';
	}

	/**
	 * 处理配置并发送POST请求
	 * @param string $url
	 * @param array  $data POST 数据数组
	 * @param array  $header 需要构造 header 内容的数组
	 */
	protected function POSTRequest($url, $data = array(), $header = array())
	{
		$options = $this->curl_opts;
		$options[CURLOPT_URL] = $url;
		$options[CURLOPT_POST] = true;
		$options[CURLOPT_POSTFIELDS] = $data;
		$options[CURLOPT_SSL_VERIFYPEER] = false;
		$options[CURLOPT_SSL_VERIFYHOST] = false;
		$options[CURLOPT_FOLLOWLOCATION] = true;
		// $options[CURLOPT_COOKIE] = '';
		$options[CURLOPT_COOKIEFILE] = $this->cookieFile;
		$options[CURLOPT_COOKIEJAR] = $this->cookieFile;
		if(!empty($header))
			$options[CURLOPT_HTTPHEADER] = $header;

		return $this->curl($options);
	}

	/**
	 * 发送 GET 请求
	 * @param string  $url
	 * @param boolean $withCookie 是否带 cookie(默认不带)
	 */
	protected function GETRequest($url, $withCookie = false)
	{
		$options = $this->curl_opts;
		$options[CURLOPT_URL] = $url;
		$options[CURLOPT_COOKIEJAR] = $this->cookieFile;

		if($withCookie)
			$options[CURLOPT_COOKIEFILE] = $this->cookieFile;

		return $this->curl($options);
	}

	/**
	 * curl 请求
	 * @param  array $options curl 选项数组
	 * @return string curl 响应结果
	 */
	protected function curl($options)
	{
		$ch = curl_init();
		curl_setopt_array($ch, $options);
		$result = curl_exec($ch);
		// echo curl_getinfo($ch, CURLINFO_HEADER_OUT);
		curl_close($ch);
		return $result;
	}

	/**
	 * 输出日志
	 * @return void
	 */
	public function log()
	{
		date_default_timezone_set('PRC');
		$this->logString .= ' '.date('Y-m-d H:i:s', time()).PHP_EOL;
		echo $this->logString;
		file_put_contents('sign.log', $this->logString, FILE_APPEND);
	}

	/**
	 * 签到失败后删除 cookie 后重试
	 * @return array 重试信息
	 */
	public function retry()
	{
		if(unlink($this->cookieFile))
			$this->isCookieExist = false;
		else
		{
			$this->logString .= '删除 cookie 失败';
			return;
		}

		$this->logString .= '签到失败，尝试删除 cookie 后重试 ';
		return array('retry'=>true);
	}

	/**
	 * 获取子类的唯一实例
	 * @return object
	 */
	public static function getInstance()
	{
		$childClass = get_called_class();
		if(is_null($childClass::$_instance))
			$childClass::$_instance = new $childClass;

		return $childClass::$_instance;
	}

	/**
	 * 防止复制唯一实例
	 * @return void
	 */
	public function __clone()
	{
		die('It\'s singleton pattern :)');
	}
}

?>