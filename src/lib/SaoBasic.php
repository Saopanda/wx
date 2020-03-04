<?php

namespace saowx\lib;

class SaoBasic {

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

        if (isset($rs->expires_time) && !$refresh) {
            if ($rs->expires_time > time()+120) {
                return $rs;
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
        $res = json_decode($res->data);
        if (isset($res->errcode)){
            $res->E_code = $res->errcode;
            return $res;
        }

        $res->E_code = 0;
        $res->timestamp = time();
        $res->expires_time = $res->timestamp + 7200;

        //  写入数据
        file_put_contents(dirname(__FILE__).'/access_token',json_encode($res));

        return $res;

    }

    /**
     * 字段验证提取器
     * @param array $data
     * @param array $fields [ 'a'=>'default' , 'b' ]
     *        可设置默认值, 无默认值且字段不存在时,返回 false
     * @return array|bool
     */
    protected function verField(array $data,array $fields)
    {
        $res = [];
        foreach ($fields as $k => $v) {
            if (is_numeric($k)){
                $field = $v;
                $default = null;
            }else{
                $field = $k;
                $default = $v;
            }
            if (isset($data[$field])){
                $res[$field] = $data[$field];
            }elseif($default == null){
                $res['res'] = $field;
                return $res;
            }else{
                $res[$field] = $default;
            }
        }
        $res['res'] = 'success';
        return $res;
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

