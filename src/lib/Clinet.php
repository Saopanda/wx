<?php


namespace saowx\lib;


class Clinet{

    private static $instance;
    /**
     * @var int 超时时间 默认30秒
     */
    private $timeout=30;
    private $CURLOPT_SSL_VERIFYHOST=false;
    private $CURLOPT_SSL_VERIFYPEER=false;

    public $result;

    private function __construct(){}

    public static function new(array $data=array())
    {
        if(is_null(self::$instance))
        {
            self::$instance = new self;

            if (isset($data['timeout'])){
                if (is_numeric($data['timeout']))
                    self::$instance->timeout = $data['timeout'];
            }
            if (isset($data['VERIFYHOST'])){
                if (is_bool($data['VERIFYHOST']))
                    self::$instance->timeout = $data['VERIFYHOST'];
            }
            if (isset($data['VERIFYPEER'])){
                if (is_bool($data['VERIFYPEER']))
                    self::$instance->timeout = $data['VERIFYPEER'];
            }

        }
        return self::$instance;
    }

    /**
     * 发送GET 请求
     * @param $url
     * @param array $params 网址参数
     * @param array $headers
     * @return mixed
     */
    public function get($url,array $params=array(),$headers=array())
    {
        $url = $this->buildUrl($url,$params);
        $res = $this->GET_DO($url,$headers);

        if ($res->data == false) {
            $res->E_code = 50009;
        }else{
            $res->E_msg = 'success';
            $res->E_code = 0;
        }
        return $res;
    }


    /**
     * 发送 Post 请求
     * @param $url
     * @param array $params
     * @param array $data
     * @return \stdClass
     */
    public function post($url,array $data,array $params = [])
    {
        $url = $this->buildUrl($url,$params);

        //  构建 post 数据
        $data = $this->POST_DATA($data);
        if ($data->E_code != 0) {
            return $data;
        }
//        var_dump($data);

        $res = $this->POST_DO($url,$data);
        if ($res->data == false) {
            $res->E_code = 50009;
        }else{
            $res->E_msg = 'success';
            $res->E_code = 0;
        }
        return $res;
    }

    protected function POST_DATA($data)
    {
        $res = new \stdClass();

        //  headers
        if (isset($data['headers'])) {
            if (is_array($data['headers'])) {
                $res->headers = $data['headers'];
            } else {
                $res->E_code = 50041;
                $res->E_msg = 'headers必须是数组';
                return $res;
            }
        }

        //  证书
        if (isset($data['pem'])) {
            if (is_file($data['pem'])) {
                $res->pem = $data['pem'];
            }else{
                $res->E_code = 50041;
                $res->E_msg = '证书错误';
                return $res;
            }
            if (isset($data['pem_key'])) {
                if (is_file($data['pem_key'])) {
                    $res->pem_key = $data['pem_key'];
                }else{
                    $res->E_code = 50041;
                    $res->E_msg = '证书密钥错误';
                    return $res;
                }
            }
        }

        //  urlEncoded
        if (isset($data['urlEncoded'])){
            if (is_array($data['urlEncoded'])) {
                $res->data = trim($this->buildUrl('',$data['urlEncoded']),'?');
                $res->E_code = 0;
                $res->E_msg = 'success';
                return $res;
            }else{
                $res->E_code = 50041;
                $res->E_msg = 'urlEncode内必须是数组';
                return $res;
            }
        }

        //  form-data
        if (isset($data['data'])) {
            if (is_array($data['data'])) {
                $res->data = $data['data'];
                foreach ($res->data as $k =>&$v){
                    if(is_file('./'.$v)){
                        $v = new \CURLFILE('./'.$v);
                    }
                }
                $res->E_code = 0;
                $res->E_msg = 'success';
                return $res;
            }else{
                $res->E_code = 50041;
                $res->E_msg = 'data内必须是数组';
                return $res;
            }
        }

        //  json
        if (isset($data['json'])) {
            if (is_array($data['json'])) {
                $res->data = json_encode($data['json'],JSON_UNESCAPED_UNICODE);
                $res->headers[] = 'Content-Type: application/json';
                $res->E_code = 0;
                $res->E_msg = 'success';
                return $res;
            }else{
                $res->E_code = 50041;
                $res->E_msg = 'json内必须是数组';
                return $res;
            }
        }

        //  raw
        if (isset($data['raw'])) {
            if (is_string($data['raw'])) {
                $res->data = $data['raw'];
                $res->E_code = 0;
                $res->E_msg = 'success';
                return $res;
            }else{
                $res->E_code = 50041;
                $res->E_msg = 'raw内必须是字符串';
                return $res;
            }
        }

        $res->E_code = 50041;
        $res->E_msg = '请填写发送内容';
        return $res;
    }

    /**
     * @param $url
     * @param $data
     * @return \stdClass
     */
    protected function POST_DO($url,$data)
    {
        $ch = curl_init();
        if(isset($data->pem)){
            //默认格式为PEM， cert
            curl_setopt($ch,CURLOPT_SSLCERTTYPE,'PEM');
            curl_setopt($ch,CURLOPT_SSLCERT,$data->pem);
        }
        if(isset($data->pem_key)){
            //默认格式为PEM， key
            curl_setopt($ch,CURLOPT_SSLKEYTYPE,'PEM');
            curl_setopt($ch,CURLOPT_SSLKEY,$data->pem_key);
        }
        if(isset($data->headers)){
            curl_setopt($ch, CURLOPT_HTTPHEADER,$data->headers);
        }
        curl_setopt($ch,CURLOPT_URL,$url);
        curl_setopt($ch,CURLOPT_POST,true);
        curl_setopt($ch,CURLOPT_POSTFIELDS,$data->data);
        curl_setopt($ch,CURLOPT_TIMEOUT,$this->timeout);
        curl_setopt($ch,CURLOPT_SAFE_UPLOAD,true);  //  禁用@上传文件
        curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
        curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,$this->CURLOPT_SSL_VERIFYHOST);
        curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,$this->CURLOPT_SSL_VERIFYPEER);
        $this->result = new \stdClass();
        $this->result->data = curl_exec($ch);
        $this->result->E_msg = curl_error($ch);
        curl_close($ch);
        return $this->result;
    }

    /**
     * @param $url
     * @param $headers
     * @return \stdClass
     */
    protected function GET_DO($url,$headers)
    {
        $ch = curl_init();
        curl_setopt($ch,CURLOPT_URL,$url);
        curl_setopt($ch,CURLOPT_TIMEOUT,$this->timeout);
        curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, $this->CURLOPT_SSL_VERIFYPEER);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, $this->CURLOPT_SSL_VERIFYHOST);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        $this->result = new \stdClass();
        $this->result->data = curl_exec($ch);
        $this->result->E_msg = curl_error($ch);
        curl_close($ch);
        return $this->result;
    }

    /**
     * 构建请求 URL
     * @param $url
     * @param $params
     * @return string
     */
    protected function buildUrl($url,$params)
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
}