<?php
require 'config.inc.php';
require 'sign.php';

/**
 * 执行签到操作类
 * @author Newton <mciguu@gmail.com>
 */
class Executor
{	
	//错误次数
	private $errCount = 0;
	//日志文本
	private $logText;
	private $state = true;
	//Weibo 实例
	private static $weibo = NULL;
	private static $token = NULL;

	/**
	 * 构造函数，记录时间戳并执行签到
	 * @param array $accounts 需要签到的账户信息数组
	 */
	public function __construct($accounts)
	{	
		date_default_timezone_set('PRC');
		$timestamp = date('Y-m-d H:i:s', time());
		$this->logText = $timestamp.PHP_EOL;
		echo $timestamp,'<br>';

		set_time_limit(60);
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
				$this->fileLoader('services/'.strtolower($svcName).'.php'); //载入要签到服务的子类
			}
			catch (Exception $e)
			{
				die($e->getMessage());
			}

			//获取实例并初始化
			$instance = $svcName::getInstance();
			$instance->init($userInfo[1], $userInfo[2]);

			$errCount = $this->errCount;
			while($errCount < RETRY_LIMIT)
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

			if($errCount >= RETRY_LIMIT)
			{
				$this->state = false;
				if(NOTIFY_FAILED)
					try
					{
						$status = $svcName.' 失败多次，请检查日志 #AutoSign#';
						$this->weiboNotify($status);
					}
					catch (Exception $e)
					{
						$instance->appendLog($e->getMessage());	
					}
			}

			//记录日志
			$this->logText .= $instance->getLog();
		}
		//输出日志
		if(LOG)
			$this->log();

		if(NOTIFY_SUCCESS && $this->state)
			$this->weiboNotify('All signed', 2);
	}

	/**
	 * 输出日志到文件
	 */
	private function log()
	{
		file_put_contents('sign.log', $this->logText, FILE_APPEND);
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

	/**
	 * 发送微博通知
	 * @param  string $status 要发布的微博内容
	 */
	private function weiboNotify($status, $visible = 0, $listId = NULL)
	{
		if(is_null(self::$weibo))
		{
			$this->fileLoader('weibo/config.php');
			$this->fileLoader('weibo/saetv2.ex.class.php');
			if(file_exists(OAUTH_FILE))
			{
				self::$token = file_get_contents(OAUTH_FILE);
				self::$token = unserialize(self::$token);
			}
			else
				throw new Exception("微博认证文件不存在");

			self::$weibo = new SaeTClientV2( WB_AKEY , WB_SKEY , self::$token['access_token']);
		}
		$weiboName = WEIBO_NAME;
		if(empty($weiboName))
			$weiboName = self::$token['name'];

		$status = '@'.$weiboName.' '.$status;
		$resp = self::$weibo->update($status, $visible, $listId);
		if(isset($resp->error_code))
			throw new Exception($resp->error);
	}
}

?>