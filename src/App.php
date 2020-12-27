<?php

namespace saopanda;

use saopanda\lib\basic;
use saopanda\lib\WXBizDataCrypt;
use saopanda\client;

class App extends basic
{
    protected $appid,$secret,$messageToken,$messageKey;
    private static $instance;

    #   小程序登陆
    protected $url = 'https://api.weixin.qq.com/sns';
    #   内容审查
    protected $url2 = 'https://api.weixin.qq.com/wxa';

    public static function new($appid,$secret,$messageToken=null,$messageKey=null)
    {
        if(is_null(self::$instance))
        {
            self::$instance = new self($appid,$secret,$messageToken,$messageKey);
        }
        return self::$instance;
    }

    public function __construct($appid,$secret,$messageToken=null,$messageKey=null)
    {
        $this->appid = $appid;
        $this->secret = $secret;
        $this->messageToken = $messageToken;
        $this->messageKey = $messageKey;
    }

    /**
     * 小程序登陆
     * @param $code
     * @return mixed
     */
    public function login($code)
    {
        $url = $this->url.'/jscode2session';
        $params = [
            'appid'=>$this->appid,
            'secret'=>$this->secret,
            'js_code'=>$code,
            'grant_type'=>'authorization_code'
        ];
        $res = client::new()->get($url,$params);
        if ($res['result']){
            $res['result'] = json_decode($res['result']);
        }
        return $res;
    }

    /**
     * 解密小程序用户信息
     * @param $data 'rawData','signature','encryptedData','iv'
     * @param $session_key
     * @return mixed
     * @throws
     */
    public function getUserInfo(array $data,$session_key)
    {
        $this->Field($data,['rawData','signature','encryptedData','iv']);

        $server_signature = sha1($data['rawData'].$session_key);
        if ($server_signature != $data['signature']) {
            return [
                'result'    =>  false,
                'errmsg'    =>  "签名验证失败",
                'errcode'   =>  6001
            ];
        }

        $res = new WXBizDataCrypt($this->appid,$session_key);
        $res = $res->decryptData($data['encryptedData'],$data['iv'],$res_data);

        if ($res != 0){
            return [
                'result'    =>  false,
                'errmsg'    =>  "密文解密失败",
                'errcode'   =>  $res
            ];
        }
        return [
            'result'    =>  json_decode($res_data,true),
            'errmsg'    =>  "",
            'errcode'   =>  0
        ];
    }

    /**
     * 解密用户手机号
     * @param $encryptedData
     * @param $iv
     * @param $session_key
     * @return mixed
     */
    public function getUserPhone($encryptedData,$iv,$session_key)
    {
        $res = new WXBizDataCrypt($this->appid,$session_key);
        $res = $res->decryptData($encryptedData,$iv,$res_data);
        $result = $this->result;
        if ($res != 0){
            $result->E_code = $res;
            $result->E_msg = '密文解密失败';
            return $result;
        }
        $result->DATA = json_decode($res_data);
        $result->E_code = 0;

        return $result;
    }

    /**
     * 生成小程序码
     * @param $scene
     * @param array $m_params  可选参数见微信文档
     * @return false|mixed|\stdClass|string
     */
    public function getQRcode($scene,array $m_params=array())
    {
        $res = $this->getAccessToken($this->appid,$this->secret);
        if ($res->E_code != 0) {
            return $res;
        }
        $url = $this->url2.'/getwxacodeunlimit';
        $params['access_token'] = $res->DATA->access_token;

        $data['json'] = [
            'scene'=>$scene,
        ];
        $data['json'] = array_merge( $data['json'],$m_params);

        $res = Clinet::new()->post($url,$data,$params);
        $rs = json_decode($res->data);

        if (is_null($rs)){
            $res->E_code = 0;
            return $res;
        }else{
            $rs->E_code = $rs->errcode;
            return $rs;
        }
    }

    //  小程序二维码
    public function getQR2code($path='pages/index/index',$m_params=array())
    {
        $res = $this->getAccessToken($this->appid,$this->secret);
        if ($res->E_code != 0) {
            return $res;
        }
        $url = 'https://api.weixin.qq.com/cgi-bin/wxaapp/createwxaqrcode';
        $params['access_token'] = $res->DATA->access_token;

        $data['json'] = [
            'path'=>$path,
        ];

        $data['json'] = array_merge( $data['json'],$m_params);

        $res = Clinet::new()->post($url,$data,$params);
        $rs = json_decode($res->data);

        if (is_null($rs)){
            $res->E_code = 0;
            return $res;
        }else{
            $rs->E_code = $rs->errcode;
            return $rs;
        }
    }

    /**
     * 微信文字检测   同步
     * @param $msg
     * @return false|mixed|\stdClass|string
     */
    public function checkText($msg)
    {
        $url = $this->url2.'/msg_sec_check';

        $res = $this->getAccessToken($this->appid,$this->secret);
        if ($res->E_code != 0) {
            return $res;
        }

        $params['access_token'] = $res->access_token;
        $data['json'] = ['content'=>$msg];
        $data['headers'] = ['coasdasdasd'];

        $res = Clinet::new()->post($url,$data,$params);
        return $this->resPak($res);
    }

    /**
     * 微信图片检测   同步
     * @param $file
     * @return false|mixed|\stdClass|string
     */
    public function checkPicture($file)
    {
        $url = $this->url2.'/img_sec_check';

        $res = $this->getAccessToken($this->appid,$this->secret);
        if ($res->E_code != 0) {
            return $res;
        }

        $params['access_token'] = $res->access_token;
        $data['data'] = ['media'=>$file];

        $res = Clinet::new()->post($url,$data,$params);
        return $this->resPak($res);
    }

    /**
     * 微信媒体检测   异步
     * @param $checkUrl
     * @param int $type    1 音频  默认2 图片
     * @return false|mixed|\stdClass|string
     */
    public function checkMedia($checkUrl,$type=2)
    {
        $url = $this->url2.'/media_check_async';

        $res = $this->getAccessToken($this->appid,$this->secret);
        if ($res->E_code != 0) {
            return $res;
        }

        $params['access_token'] = $res->access_token;

        $data['json'] = [
            'media_url'=>$checkUrl,
            'media_type'=>$type
        ];

        $res = Clinet::new()->post($url,$data,$params);
        return $this->resPak($res);
    }

    /**
     * 微信部分接口打包返回对象
     * @param $res
     * @return mixed
     */
    protected function resPak($res,$array=false)
    {
        if ($res->E_code != 0) {
            return $res;
        }
        //  通讯成功
        $result = $this->result;
        $result->DATA = json_decode($res->data,$array);
        if (isset($result->DATA->errcode)){
            $result->E_code = $result->DATA->errcode;
            $result->E_msg = $result->DATA->errmsg;
        }else{
            $result->E_code = 0;
        }
        return $result;
    }

}