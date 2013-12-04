<?php
/**
 * 签到服务基类
 * @author Newton <mciguu@gmail.com>
 */
class Sign
{
    //日志状态
    const SUCCESS     = '签到成功';
    const SIGNED      = '今天已签到';
    const FAILED      = '签到失败；';
    const LOGINFAILED = '登录失败；';

    //用户名和密码
    protected $username;
    protected $password;

    //cookie
    protected $cookieDir =  DIR_PROT;
    protected $cookieFile;
    protected $cookieName;

    //输出日志内容
    protected $logLine;

    //CURL 选项
    protected $curl_opts = array(
            CURLOPT_RETURNTRANSFER => true,
            CURLINFO_HEADER_OUT    => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_TIMEOUT        => 10,
            CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.22 (KHTML, like Gecko) Chrome/25.0.1364.58 Safari/537.22'
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
        $this->cookieName = $this->preFix.$this->username;
        $this->cookieFile = $this->cookieDir.$this->cookieName.'.cookie';
        if(!file_exists($this->cookieFile))
        {
            fopen($this->cookieFile, 'w+');
            $this->isCookieExist = false;
        }

        $this->logLine = $this->cookieName.' ';
    }

    /**
     * 发送 POST 请求
     * @param string  $url
     * @param array   $data POST 数据数组
     * @param array   $httpheader 构造 httpheader 内容的数组
     * @param boolean $header 是否输出 header 信息
     */
    protected function post($url, $data = array(), $httpheader = array(), $header = false)
    {
        $options = $this->curl_opts;
        $options[CURLOPT_URL] = $url;
        $options[CURLOPT_POST] = true;
        $options[CURLOPT_POSTFIELDS] = $data;
        $options[CURLOPT_FOLLOWLOCATION] = true;
        $options[CURLOPT_COOKIEJAR] = $this->cookieFile;
        $options[CURLOPT_COOKIEFILE] = $this->cookieFile;
        if(!empty($httpheader))
            $options[CURLOPT_HTTPHEADER] = $httpheader;
        if($header)
            $options[CURLOPT_HEADER] = true;
        return $this->curl($options);
    }

    /**
     * 发送 GET 请求
     * @param string  $url
     * @param array   httpheader 构造 httpheader 内容的数组
     * @param boolean $header 是否输出 header 信息
     */
    protected function get($url, $httpheader = array(), $header = false)
    {
        $options = $this->curl_opts;
        $options[CURLOPT_URL] = $url;
        $options[CURLOPT_FOLLOWLOCATION] = true;
        $options[CURLOPT_COOKIEJAR] = $this->cookieFile;
        $options[CURLOPT_COOKIEFILE] = $this->cookieFile;
        if(!empty($httpheader))
            $options[CURLOPT_HTTPHEADER] = $httpheader;
        if($header)
            $options[CURLOPT_HEADER] = true;
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
     * 添加日志记录
     * @param string $log 日志内容
     */
    public function appendLog($log)
    {
        $this->logLine .= $log;
    }

    /**
     * 输出日志
     */
    public function getLog()
    {
        $this->logLine .= PHP_EOL;
        echo $this->logLine,'<br>';
        return $this->logLine;
    }

    /**
     * 删除 cookie，记录失败日志并抛出异常
     * @param int $errno 错误码 0:登录失败 1:签到失败 2:自定义日志
     * @param string $log 日志内容
     */
    public function retry($errno = 1, $log = '')
    {
        switch ($errno)
        {
            case 0:
                $this->logLine .= self::LOGINFAILED;
                throw new Exception('Login failed', $errno);
                break;
            case 1:
                @unlink($this->cookieFile);
                $this->isCookieExist = false;
                $this->logLine .= empty($log) ? self::FAILED : $log;
                throw new Exception("Retry", $errno);
                break;
            case 2:
                $this->logLine .= $log;
                throw new Exception($log, $errno);
                break;
        }
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