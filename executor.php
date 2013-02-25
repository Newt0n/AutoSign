<?php
require('config.inc.php');
define('SIGN_PATH', dirname(__FILE__));

/**
 * 执行签到操作类
 * @author Newton <mciguu@gmail.com>
 */
class Executor
{	
	//允许的重试次数
	private static $retryLimit = RETRY_LIMIT;
	//是否输出日志
	private static $log = LOG;
	//失败超过上限微博通知
	private static $notify = NOTIFY;

	//错误次数
	private $errCount = 0;

	/**
	 * 构造函数
	 * @param array $accounts 需要签到的账户信息数组
	 */
	public function __construct($accounts)
	{
		//执行签到方法
		$this->execute($accounts);
	}

/**
 * 签到方法
 * @param array $accounts 需要签到的账户信息数组
 */
	public function execute($accounts)
	{
		foreach ($accounts as $userInfo)
		{
			$svcName = $userInfo[0];
			try
			{
				//载入基类和要签到服务的子类
				$this->fileLoader('sign.php');
				$this->fileLoader(strtolower($svcName).'.php');
			}
			catch (Exception $e)
			{
				echo 'Error: '.$e->getMessage();
			}

			//获取该服务实例
			$instance = $svcName::getInstance();
			//配置数据并执行签到
			$instance->init($userInfo[1], $userInfo[2]);

			$errCount = $this->errCount;
			while($errCount < self::$retryLimit)
			{
				try
				{
					$instance->sign();
				}
				catch (Exception $e)
				{
					$errCount++;
					continue;
				}

				break;
			}

			// if(self::$notify && ($errCount >= self::$retryLimit))
			// 	$this->weiboNotify($svcName);

			//输出日志到文件
			if(self::$log)
				$instance->log();
		}
	}

	/**
	 * 载入指定路径的文件
	 * @param  string $filePath
	 */
	private function fileLoader($filePath)
	{
		if(!file_exists($filePath))
			throw new Exception("文件丢失 ".$filePath, 1);
		require_once($filePath);
	}

	// private function weiboNotify($svcName)
	// {
	// 	$this->fileLoader('weibo\config.php');
	// 	$this->fileLoader('weibo\saetv2.ex.class.php');
	// 	$token = file_get_contents(SIGN_PATH.'\weibo\token.oauth');
	// 	$token = unserialize($token);
		// $weibo = new SaeTClientV2( WB_AKEY , WB_SKEY , $token['access_token']);
		
		// $ret = $weibo->update($svcName.' 失败多次，请检查日志');
	// }
}

?>