<?php

namespace saowx;

use saowx\lib\saoBasic;
use saowx\lib\ErrorCode;
use saowx\lib\WXBizDataCrypt;

class saoService extends saoBasic {

    //  微信 媒体内容安全
    public function checkMeidaAsync($media_url,$type=2)
    {
        $url ='https://api.weixin.qq.com/wxa/media_check_async';

        $accessToken = $this->getAccessToken();
        if ($accessToken['errcode'] != 0){
            $arr['code'] = ErrorCode::$WXERROR;
            $arr['mes'] = 'accessToken 拿取失败';
            return $arr;
        }

        $params['access_token'] = $accessToken['access_token'];

        $data['media_url'] = $media_url;
        $data['media_type'] = $type;

        $rs = $this->postRequest($url,$params,$data);

        $rs = json_decode($rs);

        if ($rs->errcode == 0){
            return true;
        }else{
            return false;
        }
    }


    //  微信 图片安全
    public function checkPicture($file)
    {
        $url ='https://api.weixin.qq.com/wxa/img_sec_check';

        $accessToken = $this->getAccessToken();
        if ($accessToken['errcode'] != 0){
            $arr['code'] = ErrorCode::$WXERROR;
            $arr['mes'] = 'accessToken 拿取失败';
            return $arr;
        }

        $params['access_token'] = $accessToken['access_token'];

        $data['media'] = $file;

        $rs = $this->postRequest($url,$params,$data);
        $rs = json_decode($rs);

        if ($rs->errcode == 0){
            return true;
        }else{
            return false;
        }
    }


    //  微信 文字安全
    public function checkMsg($msg)
    {
        $url ='https://api.weixin.qq.com/wxa/msg_sec_check';

        $accessToken = $this->getAccessToken();
        if ($accessToken['errcode'] != 0){
            $arr['code'] = ErrorCode::$WXERROR;
            $arr['mes'] = 'accessToken 拿取失败';
            return $arr;
        }

        $params['access_token'] = $accessToken['access_token'];

        $data['content'] = $msg;

        $rs = $this->postRequest($url,$params,$data);

        $rs = json_decode($rs);

        if ($rs->errcode == 0){
            return true;
        }else{
            return false;
        }
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

        return  $this->postRequest($url,$params,$mes);

    }

    /**
     * 小程序登录
     *
     * 请传入 code
     * @param $code
     * @return array
     */
    public function sappLogin($code)
    {
        $url = 'https://api.weixin.qq.com/sns/jscode2session';

        $params['appid'] = $this->appid;
        $params['secret'] = $this->secret;
        $params['js_code'] = $code;
        $params['grant_type'] = 'authorization_code';

        $res = $this->getRequest($url,$params);

        (isset($res['errcode'])) ? : $res['errcode'] = ErrorCode::$OK;

        return $res;
    }

    /**
     * 解密用户信息
     * @param $params ['rawData','signature','encryptedData','iv','session_key']
     * @return array
     */
    public function getUserInfo($params)
    {
        $keywords = ['rawData','signature','encryptedData','iv','session_key'];
        foreach ($keywords as $k => $v){
            if (!array_key_exists($v,$params)) {
                $arr['code'] = ErrorCode::$FIELDLACK;
                $arr['msg'] = '缺少'.$v;
                return $arr;
            }
        }

        //  数据签名校验
        $signsture_str = $params['rawData'].$params['session_key'];
        $server_signature = sha1($signsture_str);
        if ($server_signature != $params['signature']) {
            $arr['code'] = ErrorCode::$SIGN;
            $arr['msg'] = '签名验证失败';
            return $arr;
        }

        //  加密数据解密
        $jiemi = new WXBizDataCrypt($this->appid,$params['session_key']);
        $jiemi = $jiemi->decryptData($params['encryptedData'],$params['iv'],$data);

        if ($jiemi != 0){
            $arr['code'] = $jiemi;
            $arr['msg'] = '密文解密失败';
            return $arr;
        }
        $data = json_decode($data,true);

        unset($data['watermark']);
        $data['code'] = 0;
        return $data;
    }
    

}