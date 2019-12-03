<?php
namespace saoWx;


class saoBasic {

    protected $appid;

    protected $secret;

    /**
     * 用 appid 和 secret 实例化
     * @param $appid
     * @param $secret
     */
    public function __construct($appid,$secret)
    {
        $this->appid = $appid;
        $this->secret = $secret;
    }

    /**
     * 小程序登录
     * @param $code
     */
    public function sappLogin($code)
    {

    }

    

    public function getAppid()
    {
        return $this->appid;
    }

}