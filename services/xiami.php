<?php
/**
 * 虾米网签到
 * @author Newton <mciguu@gmail.com>
 */
class XiaMi extends Sign
{
    //唯一实例静态变量
    protected static $_instance = NULL;

    //服务名称前缀
    protected $preFix = 'XiaMi_';

    //cookie 存在标识
    protected $isCookieExist = true;

    //登录 URL
    private $homeUrl = 'http://www.xiami.com/';
    private $loginUrl = 'https://login.xiami.com/member/login';
    private $signAuthUrl = 'http://www.xiami.com/index/home?_=';

    //签到 URL
    private $signUrl = 'http://www.xiami.com/task/signin';
    private $allSongPlay = 'http://www.xiami.com/statclick/req/AllSongListPlay';
    private $dailyPlayList = 'http://www.xiami.com/song/playlist/id/1/type/9';
    private $dailyPoint = 'http://www.xiami.com/task/gain/type/25/id/0';
    // private $editPlayList = 'http://www.xiami.com/member/edit-playlist?ids=';
    private $httpheader = array(
                'host' => 'Host: www.xiami.com',
                'xhr' => 'X-Requested-With: XMLHttpRequest'
                );

    /**
     * 签到方法
     */
    public function sign()
    {
        if(!$this->isCookieExist)
        {
            // $this->get($this->loginUrl);
            $data = array(
                'email'=>$this->username,
                'password'=>$this->password,
                'autologin'=>1,
                'submit'=>'登 录',
                'done'=>'/',
                'type'=>''
                );
            $loginResp = $this->post($this->loginUrl, $data);
        }

        $httpheader = $this->httpheader;
        $httpheader['referer'] = 'Referer: '. $this->homeUrl;
        // 获得新 t_sign_auth
        $this->get($this->signAuthUrl.time(), $httpheader);

        // 请求每日歌单
        $httpheader['referer'] = 'Referer: http://www.xiami.com/task/all';
        $this->get('http://www.xiami.com/task/fetch-task?type=25&id=0', $httpheader);
        $this->get($this->homeUrl, $httpheader);
        $httpheader['referer'] = 'Referer: http://www.xiami.com/?task';
        $this->get($this->allSongPlay, $httpheader);
        $httpheader['referer'] = 'Referer: http://www.xiami.com/song/play?ids=/song/playlist-default';
        $songsXML = $this->get($this->dailyPlayList, $httpheader);

        // 改变播放列表
        // $dom = new DOMDocument();
        // @$dom->loadHTML($songsXML);
        // $songs = $dom->getElementsByTagName('track');
        // $songsId = array();
        // foreach ($songs as $song)
        // {
        //  $songIdNode = $song->getElementsByTagName('song_id');
        //  $songsId[] = $songIdNode->item(0)->nodeValue;
        // }
        // $this->get($this->editPlayList.implode($songsId, ','));

        // 领取每日积分
        $httpheader['referer'] = 'Referer: http://www.xiami.com/task/all';
        $this->get($this->dailyPoint);

        // 签到
        $httpheader = $this->httpheader;
        $httpheader['referer'] = 'Referer: http://www.xiami.com/home';
        $signResp = $this->post($this->signUrl, array(), $httpheader);
        if(empty($signResp) || intval($signResp) < 1)
            $this->retry();

        $this->logLine .= self::SIGNED.' 已连续签到 '.$signResp.' 天';
    }
}

?>