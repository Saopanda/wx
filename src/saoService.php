<?php

namespace saowx;

use saowx\lib\saoBasic;
use saowx\lib\ErrorCode;
use saowx\lib\WXBizDataCrypt;

class saoService extends saoBasic {

    /**
     * 微信支付统一下单
     * @param $data
     *      $data['body']       商品描述
     *      $data['total_fee']      费用
     *      $data['out_trade_no']       订单号
     *      $data['openid']         收款人
     * 以下选填
     *      $data['notify_url']     使用不同的回调地址
     *      $data['appid']          使用不同的appid
     *      $data['trade_type']     默认小程序支付
     *      $data['spbill_create_ip']   发起支付 ip
     * @return mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function wxOrder($data)
    {
        if ($this->mchid == null || $this->mchkey == null){
            $arr['code'] = ErrorCode::$FIELDLACK;
            $arr['mes'] = '微信商户未被实例化';
            return $arr;
        }
        if ( !isset($data['body']) ||  !isset($data['total_fee']) ||  !isset($data['out_trade_no']) || !isset($data['openid'])) {
            $arr['code'] = ErrorCode::$FIELDLACK;
            $arr['mes'] = '参数不对';
            return $arr;
        }
        if (!isset($data['appid'])){
            $data['appid'] = $this->appid;
        }
        if (!isset($data['notify_url'])){
            $data['notify_url'] = $this->notify_url;
        }
        if (!isset($data['trade_type'])){
            $data['trade_type'] = 'JSAPI';
        }
        if (!isset($data['spbill_create_ip'])){
            $data['spbill_create_ip'] = '1.1.1.1';
        }
        $data['mch_id'] = $this->mchid;
        $data['nonce_str'] = $this->nonce_str();
        $data['sign'] = $this->mchSign($data);
        $data = $this->arrayToXml($data);

        $url = 'https://api.mch.weixin.qq.com/pay/unifiedorder';

        $rs = $this->postRequest($url,[],$data,'raw');
        if (!is_array($rs)){
            $rs = $this->xmlToArray($rs);
        }
        return $rs;
    }

    /**
     * 微信支付 发送公众号红包
     * @param $data
     *      $data['mch_billno']     订单号
     *      $data['act_name']       活动名称
     *      $data['send_name']     发送者名称
     *      $data['re_openid']     接收红包的openid
     *      $data['total_amount']    红包金额 单位分 整数
     *      $data['wishing']        红包祝福语
     *      $data['remark']         备注
     *  以下可选
     *      $data['wxappid']        应用 appid
     *      $data['scene_id']       场景 id 红包大于200小于1元时需要
     * @return mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function redpackToUser($data)
    {
        if ($this->mchid == null || $this->mchkey == null || $this->cert == null || $this->key == null){
            $arr['code'] = ErrorCode::$FIELDLACK;
            $arr['mes'] = '微信商户未被实例化 (包含证书)';
            return $arr;
        }
        if ( !isset($data['mch_billno']) ||  !isset($data['send_name']) ||  !isset($data['re_openid']) || !isset
        ($data['total_amount']) || !isset($data['wishing']) || !isset($data['remark'])) {
            $arr['code'] = ErrorCode::$FIELDLACK;
            $arr['mes'] = '参数不对';
            return $arr;
        }

        if (!isset($data['wxappid'])){
            $data['wxappid'] = $this->appid;
        }

        $url = 'https://api.mch.weixin.qq.com/mmpaymkttransfers/sendredpack';

        $data['mch_id'] = $this->mchid;
        $data['client_ip'] = '1.1.1.1';
        $data['total_num'] = 1;
        $data['nonce_str'] = $this->nonce_str();
        $data['sign'] = $this->mchSign($data);
        $data = $this->arrayToXml($data);

        $rs = $this->postRequest($url,[],$data,'raw','/home/code/apiclient_cert.pem','/home/code/apiclient_key.pem');

        if (!is_array($rs)){
            $rs = $this->xmlToArray($rs);
        }
        return $rs;
    }


    /**
     * 企业付款到零钱
     * @param $data
     *      $data['partner_trade_no']       订单号
     *      $data['openid']                 收钱用户
     *      $data['amount']                 金额
     *      $data['desc']                   打款备注
     * 以下非必填
     *      $data['mch_appid']              应用 appid
     * @return mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function costToUser($data)
    {
        if ($this->mchid == null || $this->mchkey == null || $this->cert == null || $this->key == null){
            $arr['code'] = ErrorCode::$FIELDLACK;
            $arr['mes'] = '微信商户未被实例化 (包含证书)';
            return $arr;
        }

        if ( !isset($data['partner_trade_no']) ||  !isset($data['openid']) || !isset
            ($data['amount']) || !isset($data['desc'])) {
            $arr['code'] = ErrorCode::$FIELDLACK;
            $arr['mes'] = '参数不对';
            return $arr;
        }

        if (!isset($data['mch_appid'])){
            $data['mch_appid'] = $this->appid;
        }

        $url = 'https://api.mch.weixin.qq.com/mmpaymkttransfers/promotion/transfers';

        $data['mchid'] = $this->mchid;
        $data['spbill_create_ip'] = '1.1.1.1';
        $data['check_name'] = 'NO_CHECK';
        $data['nonce_str'] = $this->nonce_str();
        $data['sign'] = $this->mchSign($data);
        $data = $this->arrayToXml($data);

        $rs = $this->postRequest($url,[],$data,'raw',$this->cert,$this->key);

        if (!is_array($rs)){
            $rs = $this->xmlToArray($rs);
        }
        return $rs;

    }

    //  微信媒体内容安全    异步
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

        if (!is_array($rs)){
            $rs = json_decode($rs,true);
        }

        if ($rs['errcode'] == 0){
            return true;
        }else{
            return false;
        }
    }

    /**
     * 微信图片安全   同步
     * @param $file  文件路径 可以是临时路径
     * @return bool
     */
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

        $data['media'] = fopen($file,'r');

        $rs = $this->postRequest($url,$params,$data,'multipart');
        if (!is_array($rs)){
            $rs = json_decode($rs,true);
        }

        if ($rs['errcode'] == 0){
            return true;
        }else{
            return false;
        }
    }


    /**
     * 微信文字安全   同步
     * @param $msg   待检查文字
     * @return bool
     */
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
        $data = json_encode($data,JSON_UNESCAPED_UNICODE);

        //  中文会被转义 自己 json_encode 使用 raw 发送
        $rs = $this->postRequest($url,$params,$data,'raw');
        if (!is_array($rs)){
            $rs = json_decode($rs,true);
        }

        if ($rs['errcode'] == 0){
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

        $rs = $this->postRequest($url,$params,$mes);
        if (!is_array($rs)){
            $rs = json_decode($rs,true);
        }

        return $rs;

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
        if (!is_array($res)){
            $res = json_decode($res,true);
        }


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