<?php

namespace saopanda\lib;


class basic {

    protected static $app,$pay,$pay3;
    protected $appid,$secret,$messageToken,$messageKey;
    protected $mchId,$mchKey,$notify_url,$mchCert,$mchCertKey,$currency;

    public function __construct($appid,$secret,$mchId,$mchKey,$notify_url,
        $currency,$mchCert,$mchCertKey,$messageToken,$messageKey)
    {
        $this->appid = $appid;
        $this->secret = $secret;
        $this->mchId = $mchId;
        $this->mchKey = $mchKey;
        $this->notify_url = $notify_url;
        $this->currency = $currency;
        $this->mchCert = $mchCert;
        $this->mchCertKey = $mchCertKey;
        $this->messageToken = $messageToken;
        $this->messageKey = $messageKey;
    }

    //  文件流转文件
    protected function saveToFile($string,$path,$name=null)
    {
        $name=null ? $name="file_".uniqid():$name;
        $file = fopen("./".$path.$name,"w");
        fwrite($file,$string);
        fclose($file);
        return $path.$name;
    }

    protected function checkError($client)
    {
        if (!$client['result']) {
            return $client;
        }
        $data = json_decode($client['result'], true);
        if (isset($data['errcode'])) {
            $client = [
                'result'    =>  false,
                "errmsg"  => $data['errmsg'],
                "errcode" => $data['errcode']
            ];
            return $client;
        }
        $client['result'] = $data;
        return $client;
    }

    /**
     * 字段验证
     * @param array $data
     * @param array $fields
     * @throws
     */
    protected static function Field(array $data,array $fields)
    {
        foreach ($fields as $k => $v) {
            if (!isset($data[$v])){
                throw new \Exception('DATA缺少'.$v);
            }
        }
    }

    /**
     * 随机字符串
     * @param int $a
     * @return string
     */
    protected function nonce_str($a=32){
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
    protected function xmlToArray($xml)
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
    protected function arrayToXml($arr)
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
     * @param $mchKey
     * @return string
     */
    protected function mchSign($data,$mchKey){
        $stringA = '';
        ksort($data);
        foreach ($data as $key=>$value){
            if(!$value) continue;
            if($stringA) $stringA .= '&'.$key."=".$value;
            else $stringA = $key."=".$value;
        }
        $stringSignTemp = $stringA.'&key='.$mchKey;
        return strtoupper(md5($stringSignTemp));
    }

}

