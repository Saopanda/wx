<?php

namespace saowx;


class SaoService {

    private static $app,$pay;

    private function __construct(){}

    /**
     * 实例化小程序
     * @param $appid
     * @param $secret
     * @param $messageToken
     * @param $messageKey
     * @return AppService
     */
    static function app($appid,$secret,$messageToken=null,$messageKey=null)
    {
        if (is_null(self::$app)){
            self::$app = new AppService($appid,$secret,$messageToken,$messageKey);
        }
        return self::$app;
    }

    /**
     * 实例化微信支付
     * @param $appid        //  接入支付的appid
     * @param $mchId        //  商户id
     * @param $mchKey       //  商户密钥
     * @param $notify_url   //  回调通知
     * @param $trade_type   //  支付类型 默认 JSAPI
     * @param $mchCert      //  商户证书
     * @param $mchCertKey   //  商户证书密钥
     * @return PayService
     */
    static function pay($appid,$mchId,$mchKey,$notify_url,$trade_type='JSAPI',$mchCert=null,$mchCertKey=null)
    {
        if (is_null(self::$pay)){
            self::$pay = new PayService($appid,$mchId,$mchKey,$notify_url,$trade_type,$mchCert,$mchCertKey);
        }
        return self::$pay;
    }



    /**
     * 微信消息推送 验证服务器
     *
     * @param array $data['signature'] $data['timestamp'] $data['nonce']
     * @param $token
     * @return bool
     */
    public function wxMesVerify(array $data,$token)
    {
        if (!isset($data["signature"]) || isset($data["timestamp"]) || isset($data["nonce"])) {
            $arr['code'] = ErrorCode::$FIELDLACK;
            $arr['mes'] = false;
            return $arr;
        }

        $tmpArr = array($token, $data['timestamp'], $data['nonce']);

        sort($tmpArr, SORT_STRING);
        $tmpStr = implode( $tmpArr );
        $tmpStr = sha1( $tmpStr );

        if ($tmpStr == $data['signature'] ) {
            $arr['code'] = 0;
            $arr['mes'] = true;
            return $arr;
        } else {
            $arr['code'] = ErrorCode::$SIGN;
            $arr['mes'] = false;
            return $arr;
        }

    }

    /**
     *
     * 发送小程序客服消息
     * @param string $openid
     * @param array $data 示例 [ 'text' => [ 'content' => 'niubi' ] ]
     * 其他参考微信文档 https://developers.weixin.qq.com/miniprogram/dev/api-backend/open-api/customer-message/customerServiceMessage.send.html
     *
     * @return mixed
     * @throws
     */
    public function sendMessage($openid,array $data)
    {
        $accessToken = $this->getAccessToken();
        if ($accessToken['errcode'] != 0){
            $arr['code'] = ErrorCode::$WXERROR;
            $arr['mes'] = 'accessToken 拿取失败';
            return $arr;
        }

        $url = "https://api.weixin.qq.com/cgi-bin/message/custom/send";
        $params['access_token'] = $accessToken['access_token'];

        reset($data);
        $data['msgtype'] = key($data);
        $data['touser'] = $openid;

        $mes = array_merge([
            'text'=>[
                'content'=>null,
            ],
            'image'=>[
                'media_id'=>null,
            ],
            'link'=>[
                'title'=>null,
                'description'=>null,
                'url'=>null,
                'thumb_url'=>null,
            ],
            'miniprogrampage'=>[
                'title'=>null,
                'pagepath'=>null,
                'thumb_media_id'=>null,
            ],
        ],$data);

        $rs = $this->postRequest($url,$params,$mes);
        if (!is_array($rs)){
            $rs = json_decode($rs,true);
        }

        return $rs;

    }

}