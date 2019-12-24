# wxservice
PHP版本的微信开发服务 小程序，公众号等

致力于成为一个功能齐全 使用简单的轮子，目前功能较少，正在缓慢开发中

Composer：<a href="https://packagist.org/packages/saopanda/saowx" style=" text-decoration-line: none;font-size: 14px; white-space: normal;">saopanda/saowx</a>

引用了:
@微信官方 的解密文件 WXBizDataCrypt.php <a href="http://undefined" style=" text-decoration-line: none;font-size: 14px; white-space: normal;">点击下载</a>

test文件夹内有示例代码，适合直接上手

## 实例化
```
    use saowx\saoService;
    $wx = new saoService('appid','secret');
```
## 小程序登录
```
   $res =  $wx->sappLogin('CODE');
```
## 解密小程序用户信息
```
    $data = [
        'session_key'=>'',
        'rawData'=>'',
        'signature'=>'',
        'encryptedData'=>'',
        'iv'=>'',
    ];
    $res = $wx->getUserInfo($data);
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

