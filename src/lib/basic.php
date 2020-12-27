<?php

namespace saopanda\lib;

class basic {

    //  文件流转文件
    public function saveToFile($string,$path,$name=null)
    {
        $name=null ? $name="file_".uniqid():$name;
        $file = fopen("./".$path.$name,"w");
        fwrite($file,$string);
        fclose($file);
        return $path.$name;
    }

    /**
     * 获取 access_token
     * @param $appid
     * @param $secret
     * @param bool $refresh
     * @return false|mixed|string
     */
    protected function getAccessToken($appid, $secret, $refresh = false)
    {
        $rs = @file_get_contents(dirname(__FILE__).'/access_token');
        $rs = json_decode($rs);
        $result = $this->result;

        if (isset($rs->expires_time) && !$refresh) {
            if ($rs->expires_time > time()+120) {
                $result->DATA = $rs;
                $result->E_code = 0;
                return $result;
            }
        }

        $url = 'https://api.weixin.qq.com/cgi-bin/token';
        $params = [
            'grant_type'=>'client_credential',
            'appid'=>$appid,
            'secret'=>$secret,
        ];

        $res = Clinet::new()->get($url,$params);
        if ($res->E_code != 0) {
            return $res;
        }
        //  通讯成功
        $res_data = json_decode($res->data);
        if (isset($res_data->errcode)){
            $res->E_code = $res_data->errcode;
            return $res;
        }
        $res_data->timestamp = time();
        $res_data->expires_time = $res_data->timestamp + 7200;
        $result->E_code = 0;
        $result->DATA = $res_data;
        //  写入数据
        file_put_contents(dirname(__FILE__).'/access_token',json_encode($res_data));

        return $result;
    }

    /**
     * 字段验证
     * @param array $data
     * @param array $fields
     * @throws
     */
    public function Field(array $data,array $fields)
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

