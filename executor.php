<?php
/**
 * 执行签到操作类
 * @author Newton <mciguu@gmail.com>
 */
class Executor
{
	/**
	 * 构造函数
	 * @param array $config 需要签到的账户信息数组
	 */
	public function __construct($config)
	{
		//执行签到方法
		$this->execute($config);
	}

/**
 * 签到方法
 * @param array $config 需要签到的账户信息数组
 * @return void
 */
	public function execute($config)
	{
		foreach ($config as $userInfo)
		{
			$svcName = $userInfo[0];
			try {
				//载入基类和要签到服务的子类
				$this->fileLoader('sign.php');
				$this->fileLoader(strtolower($svcName).'.php');
			} catch (Exception $e) {
				echo 'Error: '.$e->getMessage();
			}

			//获取该服务实例
			$instance = $svcName::getInstance();
			//配置数据并执行签到
			$instance->init($userInfo[1], $userInfo[2]);
			$msg = $instance->sign();
			//签到失败后尝试删除 cookie 重试
			if(isset($msg['retry']))
				$instance->sign();

			//输出日志到文件
			if(!isset($config['log']) || $config['log'])
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
			throw new Exception("文件丢失", 1);
		require_once($filePath);
	}
}

?>