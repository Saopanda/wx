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
     * 商户号 $mchid
     * @var
     */
    protected $mchid;

    /**
     * 商户秘钥 $mchkey
     * @var
     */
    protected $mchkey;


    /**
     * 商户证书 $cert
     * @var
     */
    protected $cert;


    /**
     * 商户证书秘钥 $key
     * @var
     */
    protected $key;

    /**
     * http 客户端
     * @GuzzleHttp
     * @var Client
     */
    protected $client;

    /**
     * 微信支付商户回调地址
     * @var
     */
    protected $notify_url;

    /**
     * 用 appid 和 secret 实例化
     * @param $data
     *      $data['appid']
     *      $data['secret']
     * 以下在使用微信支付时需要
     *      $data['mchid']
     *      $data['mchkey']
     *      $data['notify_url']  回调地址
     *      $data['cert']  商户支付证书  apiclient_cert.pem
     *      $data['key']  商户支付证书  apiclient_key.pem
     * @param $secret
     */
    public function __construct(array $data)
    {
        if ( !isset($data['appid']) ||  !isset($data['secret'])) {
            $arr['code'] = ErrorCode::$FIELDLACK;
            $arr['mes'] = '参数不对';
            return $arr;
        }
        foreach ($data as $k =>$v) {
            $this->$k = $v;
        }
        $this->client = new Client();
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
    public function buildParams($data,$type,$cert,$key)
    {
        $data = $this->objectToArray($data);
        if (!is_null($cert)){
            $res['cert'] = $cert;
        }
        if (!is_null($key)){
            $res['ssl_key'] = $key;
        }
        switch ($type) {
            case "json":
                $res['json'] = $data;
                break;
            case "form_params":
                $res['form_params'] = $data;
                break;
            case "multipart":
                foreach ($data as $k => $v) {
                    $tmp['name'] = $k;
                    $tmp['contents'] = $v;
                    $res['multipart'][] = $tmp;
                }
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
        if (!is_array($res)){
            $res = json_decode($res,true);
        }

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
            return $res->getBody()->getContents();

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
     * @param string $type
     *      json        :   以 json 格式
     *      form_params :   application/x-www-form-urlencoded   :   表单
     *      multipart   :   multipart/form-data                 :   多文件表单 => 文件必须是 fopen返回的资源
     *      raw         :   原始数据
     * @param array $cert
     *      ['/path/123.pem','password'] || '/path/123.pem'
     * @param array $key
     *      ['/path/123.pem','password'] || '/path/123.pem'
     * @return mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function postRequest($url,array $params,$data,$type='json',$cert=null,$key=null)
    {
        $data = $this->buildParams($data,$type,$cert,$key);
        var_dump($data);
        try {
            $url = $this->buildUrl($url,$params);
            $res = $this->client->request('post',$url,$data);

            return $res->getBody()->getContents();

        }catch(RequestException $exception){

            $e =  $exception->getHandlerContext();
            $arr['errmsg'] = $e['error'];
            $arr['errcode'] = ErrorCode::$CLIENT;
            return $arr;
        }
    }

    /**
     * 随机字符串
     * @param int $a
     * @return string
     */
    public function nonce_str($a=32){
        $result = '';
        $str = 'QWERTYUIOPASDFGHJKLZXVBNMqwertyuioplkjhgfdsamnbvcxz';
        for ($i=0;$i<$a;$i++){
            $result .= $str[mt_rand(0,48)];
        }
        return $result;
    }

    /**
     * xml 转数组
     * @param $xml
     * @return mixed
     */
    public function xmlToArray($xml)
    {
        libxml_disable_entity_loader(true);
        $values = json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
        return $values;
    }

    /**
     * 数组转换xml
     * @param $arr
     * @return string
     */
    public function arrayToXml($arr)
    {
        $xml = "<xml>";
        foreach ($arr as $key=>$val)
        {
            if (is_numeric($val)){
                $xml.="<".$key.">".$val."</".$key.">";
            }else{
                $xml.="<".$key."><![CDATA[".$val."]]></".$key.">";
            }
        }
        $xml.="</xml>";
        return $xml;
    }

    /**
     * 微信商户签名函数
     * @param $data
     * @return string
     */
    public function mchSign($data){
        $stringA = '';
        ksort($data);
        foreach ($data as $key=>$value){
            if(!$value) continue;
            if($stringA) $stringA .= '&'.$key."=".$value;
            else $stringA = $key."=".$value;
        }
        $stringSignTemp = $stringA.'&key='.$this->mchkey;
        return strtoupper(md5($stringSignTemp));
    }



}

