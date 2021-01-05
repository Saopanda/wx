<?php

namespace saopanda;

use saopanda\lib\basic;

class Pay extends basic
{

    protected $url = 'https://api.mch.weixin.qq.com/pay/';
    protected $urlV3 = 'https://api.mch.weixin.qq.com/v3/pay/transactions/';


    /**
     * @param $appid    //  商户绑定的微信应用
     * @param $mchId    //  商户ID
     * @param $mchKey   //  商户密钥
     * @param $notify_url   //  结果回调地址
     * @param null $mchCert     //  证书
     * @param null $mchCertKey  //  证书密钥
     * @return Pay
     */
    public static function new($appid,$mchId,$mchKey,$notify_url,$mchCert=null,$mchCertKey=null)
    {
        if(is_null(self::$pay))
        {
            self::$pay = new self($appid,null,$mchId,$mchKey,$notify_url,null,$mchCert
            ,$mchCertKey,null,null);
        }
        return self::$pay;
    }
    /**
     * @param $appid    //  商户绑定的微信应用
     * @param $mchId    //  商户ID
     * @param $mchKey   //  商户密钥
     * @param $notify_url   //  结果回调地址
     * @param string $currency  //  币种
     * @param null $mchCert     //  证书
     * @param null $mchCertKey  //  证书密钥
     * @return Pay
     */
    public static function newV3($appid,$mchId,$mchKey,$notify_url,$currency='CNY',$mchCert=null,$mchCertKey=null)
    {
        if(is_null(self::$pay3))
        {
            self::$pay3 = new self($appid,null,$mchId,$mchKey,$notify_url,$currency,$mchCert
            ,$mchCertKey,null,null);
        }
        return self::$pay3;
    }

    /**
     * 统一下单 v2
     *
     * @param $type         //  JSAPI 小程序 ｜NATIVE ｜APP ｜MWEB
     * @param $openid       //  谁的订单
     * @param $body         //  什么东西
     * @param $total_fee    //  多少钱
     * @param $out_trade_no //  自定义订单号
     * @param $m_params     //  其他参数见微信支付文档v2
     * @return array|mixed
     */
    public static function order($type,$openid,$body,$total_fee,$out_trade_no,array $m_params=array())
    {
        $data = [
            'openid'=>$openid,
            'body'=>$body,
            'total_fee'=>$total_fee,
            'out_trade_no'=>$out_trade_no,
            'notify_url'=>self::$pay->notify_url,
            'appid'=>self::$pay->appid,
            'trade_type'=>$type,
            'mch_id'=>self::$pay->mchId,
            'spbill_create_ip'=>'1.1.1.1',
            'nonce_str'=>self::$pay->nonce_str(),
        ];

        $data = array_merge($data,$m_params);
        $data['sign'] = self::$pay->mchSign($data,self::$pay->mchKey);
        $data = self::$pay->arrayToXml($data);

        $url = self::$pay->url.'unifiedorder';

        $res = client::new()->rawData($data)->post($url);
        if ($res['result']){
            $res['result'] = self::$pay->xmlToArray($res['result']);
            if (isset($res['result']['result_code'])){
                if ($res['result']['result_code'] != 'SUCCESS'){
                    $res['errcode'] = 99;
                    $res['errmsg'] = $res['result']['err_code_des'];
                }else{
                    $sign['appId'] = self::$pay->appid;
                    $sign['timeStamp'] = (string)time();
                    $sign['nonceStr'] = self::$pay->nonce_str();
                    $sign['package'] = 'prepay_id='.$res['result']['prepay_id'];
                    $sign['signType'] = 'MD5';
                    $sign['sign'] = self::$pay->mchSign($sign,self::$pay->mchKey);
                    $res['result'] = $sign;
                }
            }else{
                $res['errcode'] = 98;
                $res['errmsg'] = $res['result']['return_msg'];
            }
        }
        return $res;
    }

    protected function orderV3($type,$openid,$description,$total,$out_trade_no, $m_params=array())
    {
        $data = [
            'appid'=>$this->appid,
            'mchid'=>$this->mchId,
            'description'=>$description,
            'out_trade_no'=>$out_trade_no,
            'notify_url'=>$this->notify_url,
            'amount'=>[
                'total' => $total,
                'currency'=>$this->currency
            ],
            'payer' =>  [
                'openid'=>$openid
            ],
            'trade_type'=>$this->trade_type,
            'spbill_create_ip'=>'1.1.1.1',
            'nonce_str'=>$this->nonce_str(),
        ];

        $data = array_merge($data,$m_params);
        $data['sign'] = $this->mchSign($data,self::$pay->mchKey);

        $url = self::$pay->url.$type;

        return client::new()
            ->headers(['Accept: application/json','User-Agent: PC'])
            ->jsonData($data)
            ->post($url);
    }

    /**
     * 企业付款到零钱
     *
     * @param $openid           //  转给谁
     * @param $amount           //  多少钱
     * @param $desc             //  转款说明
     * @param $partner_trade_no //  订单号
     * @param $m_params         //  可选参数 见微信支付文档
     * @return array|mixed
     */
    public function costToUser($openid,$amount,$desc,$partner_trade_no,array $m_params=array())
    {
        if (is_file($this->mchCert) || is_file($this->mchCertKey)){
            $this->result->E_code = 50042;
            $this->result->E_msg = '证书不存在';
            return $this->result;
        }

        $data = [
            'openid'=>$openid,
            'amount'=>$amount,
            'desc'=>$desc,
            'partner_trade_no'=>$partner_trade_no,
            'mch_appid'=>$this->appid,
            'mchid'=>$this->mchId,
            'nonce_str'=>$this->nonce_str(),
            'check_name'=>'NO_CHECK',
            'spbill_create_ip'=>'1.1.1.1',
        ];
        $data = array_merge($data,$m_params);
        $data['sign'] = $this->mchSign($data,$this->mchKey);
        $data = $this->arrayToXml($data);

        $url = 'https://api.mch.weixin.qq.com/mmpaymkttransfers/promotion/transfers';
        $data = [ 'raw' => $data ];
        $data['pem'] = $this->mchCert;
        $data['pem_key'] = $this->mchCertKey;

        $res = Clinet::new()->post($url,$data);

        if ($res->E_code == 0) {
            $res->data = $this->xmlToArray($res->data);
        }
        return $res;

    }

    /**
     * 发放现金红包
     *
     * @param $openid           //  发给谁
     * @param $amount           //  金额
     * @param $desc             //  红包祝福语
     * @param $send_name        //  谁发的
     * @param $act_name         //  活动名称
     * @param $remark           //  备注
     * @param $mch_billno       //  订单号
     * @param $m_params         //  可选参数 见微信支付文档
     * @param array $m_params
     * @return array|mixed|\stdClass
     */
    public function redpackToUser($openid,$amount,$desc,$send_name,$act_name,$remark,$mch_billno,array
    $m_params=array())
    {
        if (is_file($this->mchCert) || is_file($this->mchCertKey)){
            $this->result->E_code = 50042;
            $this->result->E_msg = '证书不存在';
            return $this->result;
        }

        $data = [
            're_openid'=>$openid,
            'total_amount'=>$amount,
            'wishing'=>$desc,
            'send_name'=>$send_name,
            'act_name'=>$act_name,
            'remark'=>$remark,
            'mch_billno'=>$mch_billno,
            'wxappid'=>$this->appid,
            'mch_id'=>$this->mchId,
            'nonce_str'=>$this->nonce_str(),
            'check_name'=>'NO_CHECK',
            'client_ip'=>'1.1.1.1',
            'total_num'=>1,
        ];
        $data = array_merge($data,$m_params);
        $data['sign'] = $this->mchSign($data,$this->mchKey);
        $data = $this->arrayToXml($data);

        $url = 'https://api.mch.weixin.qq.com/mmpaymkttransfers/sendredpack';
        $data = [ 'raw' => $data ];
        $data['pem'] = $this->mchCert;
        $data['pem_key'] = $this->mchCertKey;

        $res = Clinet::new()->post($url,$data);

        if ($res->E_code == 0) {
            $res->data = $this->xmlToArray($res->data);
        }
        return $res;
    }
}