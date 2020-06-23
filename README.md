# wxservice
好用的 PHP微信开发服务 小程序，公众号，微信商户，直接使用！

Composer：<a href="https://packagist.org/packages/saopanda/saowx" style=" text-decoration-line: none;font-size: 14px; white-space: normal;">saopanda/saowx</a>

使用了:
@微信官方 的解密文件 WXBizDataCrypt.php <a href="http://undefined" style=" text-decoration-line: none;font-size: 14px; white-space: normal;">点击下载</a>

test中包含示例代码

## 实例化
使用消息服务时，传入 token和 key
使用需要证书的微信商户接口时，需要传 证书和 密钥
```
    use saowx\saoService;
    //  小程序
    $wx = saoService::app($appid,$secret[,$messageToken,$messageKey]);
    //  微信支付
    $pay = saoService::pay($appid,$mchId,$mchKey,$notify_url[,$trade_type,$mchCert,$mchCertKey])
```
## 约定
* 所有接口统一返回对象。格式如下
```
    {
        "E_code": 成功是 0
        "E_msg": 失败时有此字段
        "DATA"：返回内容主体
    }
```

## 小程序登录
* 需要CODE
```
   $res =  $wx->sappLogin('CODE');
   
   {
       "DATA": {
           "errcode": 40029,
           "errmsg": "invalid code, hints: [ req_id: mGeCU0eNRa-24Dm6a ]"
       },
       "E_code": 40029,
       "E_msg": "invalid code, hints: [ req_id: mGeCU0eNRa-24Dm6a ]"
   }
```
## 解密小程序用户信息
方便新增用户，DATA内为数组，字段为微信默认
```
    $data = [
        'session_key'=>'',
        'rawData'=>'',
        'signature'=>'',
        'encryptedData'=>'',
        'iv'=>'',
    ];
    $res = $wx->getUserInfo($data);
    
    {
        "E_code":0,
        "DATA":[
            "openId":"xxxxx",
            ....
        ]
    }
```




## 发送小程序客服消息
微信官方格式，选择对应的一种进行发送
* 发送文字
```
    'text'=>[
        'content' => 'niubi'
    ]
```
* 发送素材库的图片
```
    'image'=>[
        'media_id'=>'',
    ]
```
* 发送图文链接
```
    'link'=>[
        'title'=>'',
        'description'=>'',
        'url'=>'',
        'thumb_url'=>'',
    ]
```
* 发送小程序卡片
```
    'miniprogrampage'=>[
        'title'=>'',
        'pagepath'=>'',
        'thumb_media_id'=>'',
    ]
```
使用
```    
    $data = [
        'text'=>[
            'content' => 'niubi'
        ]
    ];
    $toUser = 'openid';
    $res = $wx->sendMessage($toUser,$data);

```

### 错误码

E_code | 错误
|---|---| 
50009 | curl 网络故障
50011 | 签名验证失败
50041 | 参数错误
50042 | 证书不存在
50050 | 方法不存在