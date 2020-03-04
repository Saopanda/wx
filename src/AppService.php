<?php

namespace saowx;

use saowx\lib\SaoBasic;
use saowx\lib\WXBizDataCrypt;
use saowx\lib\Clinet;

class AppService extends SaoBasic
{
    protected $appid,$secret;

    #   小程序登陆
    protected $url = 'https://api.weixin.qq.com/sns';
    #   内容审查
    protected $url2 = 'https://api.weixin.qq.com/wxa';

    public function __construct($appid,$secret)
    {
        $this->appid = $appid;
        $this->secret = $secret;
    }

    /**
     * 小程序登陆
     * @param $code
     * @return mixed
     */
    public function login($code)
    {
        $url = $this->url.'/jscode2session';
        require_once '';
        $params = [
            'appid'=>$this->appid,
            'secret'=>$this->secret,
            'js_code'=>$code,
            'grant_type'=>'authorization_code'
        ];
        $res = Clinet::new()->get($url,$params);
        return $this->resPak($res);
    }

    /**
     * 解密小程序用户信息
     * @param $rawData
     * @param $signature
     * @param $encryptedData
     * @param $iv
     * @param $session_key
     * @return mixed
     */
    public function getUserInfo($rawData,$signature,$encryptedData,$iv,$session_key)
    {
        //  数据签名校验
        $server_signature = sha1($rawData.$session_key);
        $result = new \stdClass();
        if ($server_signature != $signature) {
            $result->E_code = '50011';
            $result->E_msg = '签名验证失败';
            return $result;
        }
        //  加密数据解密
        $res = new WXBizDataCrypt($this->appid,$session_key);
        $res = $res->decryptData($encryptedData,$iv,$data);

        if ($res != 0){
            $result->E_code = $res;
            $result->E_msg = '密文解密失败';
            return $result;
        }
        $data = json_decode($data);
        $data->E_code = 0;

        return $data;
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
        $params['access_token'] = $res->access_token;

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
     * 打包返回对象
     * @param $res
     * @return mixed
     */
    protected function resPak($res)
    {
        if ($res->E_code != 0) {
            return $res;
        }
        //  通讯成功
        $rs = json_decode($res->data);
        if (isset($rs->errcode)){
            $rs->E_code = $rs->errcode;
        }else{
            $rs->E_code = 0;
        }
        return $rs;
    }

}