<?php

namespace saowx\lib;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class saoBasic {

    /**
     * 小程序 appid
     * @var
     */
    protected $appid;

    /**
     * 小程序 secret
     * @var
     */
    protected $secret;

    /**
     * http 客户端
     * @GuzzleHttp
     * @var Client
     */
    protected $client;

    /**
     * 用 appid 和 secret 实例化
     * @param $appid
     * @param $secret
     */
    public function __construct($appid,$secret)
    {
        $this->appid = $appid;
        $this->secret = $secret;

        $this->client = new Client([
            'timeout' => '3'
        ]);
    }

    /**
     * 构建请求网址
     */
    public function buildUrl($url,$params)
    {
        $num = strrpos($url,'?');
        if ( $num ){
            $url = substr($url,0,$num).'?';
        }else{
            $url .= '?';
        }

        foreach ($params as $kk => $vv) {
            $url .= $kk .'='.$vv.'&';
        }

        $url = trim($url,'&');
        return $url;

    }

    /**
     * 构建 请求数据
     */
    public function buildParams($data,$type)
    {
        $data = $this->objectToArray($data);
        switch ($type) {

            case "json":
                $res['json'] = $data;
                break;
            case "form_params":
                $res['form_params'] = $data;
                break;
            case "multipart":
                $res['multipart'] = $data;
                break;
            case "raw":
                $res['body'] = $data;
                break;
            default:
                $res['body'] = $data;
                break;
        }

        return $res;
    }

    /**
     * 转换数组
     */
    public function objectToArray($d) {
        if (is_object($d)) {
            $d = get_object_vars($d);
        }

        if (is_array($d)) {
            return array_map([__CLASS__,__FUNCTION__], $d);
        }
        else {
            return $d;
        }
    }

    /**
     * 获取小程序 accesstoken
     */
    public function getAccessToken($refresh = false)
    {
        $rs = @file_get_contents(dirname(__FILE__).'/access_token');
        $rs = json_decode($rs,true);

        if (isset($rs['expires_time']) && !$refresh) {
            if ($rs['expires_time'] > time()+120) {
                return $rs;
            }
        }

        $url = 'https://api.weixin.qq.com/cgi-bin/token';
        $params['grant_type'] = 'client_credential';
        $params['appid'] = $this->appid;
        $params['secret'] = $this->secret;

        $res = $this->getRequest($url,$params);
        if (!isset($res['errcode'])){
            $res['errcode'] = 0;
            $res['timestamp'] = time();
            $res['expires_time'] = $res['timestamp'] + 7200;
        }

        //  写入数据
        file_put_contents(dirname(__FILE__).'/access_token',json_encode($res));

        return $res;

    }

    /**
     * 发起 get api 请求
     */
    public function getRequest($url,array $params)
    {
        try {

            $url = $this->buildUrl($url,$params);
            $res = $this->client->request('get',$url);
            return json_decode($res->getBody()->getContents(),true);

        }catch(RequestException $exception){

            $e =  $exception->getHandlerContext();
            $arr['errmsg'] = $e['error'];
            $arr['errcode'] = ErrorCode::$CLIENT;
            return $arr;
        }
    }

    /**
     *
     * 发起 post api 请求
     * 发送格式默认为json
     *
     * @param $url
     * @param array $params
     * @param array $data
     * @param string $type json | raw | form_params | multipart
     * @return mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function postRequest($url,array $params,array $data,$type='json')
    {

        $data = $this->buildParams($data,$type);

        try {
            $url = $this->buildUrl($url,$params);
            $res = $this->client->request('post',$url,$data);
            return json_decode($res->getBody()->getContents(),true);

        }catch(RequestException $exception){

            $e =  $exception->getHandlerContext();
            $arr['errmsg'] = $e['error'];
            $arr['errcode'] = ErrorCode::$CLIENT;
            return $arr;
        }
    }







}

