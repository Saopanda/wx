# saopanda/wx

微信小程序API简易开发

可能是最简单、好用易上手的微信服务器端sdk
https://github.com/Saopanda/wx
https://packagist.org/packages/saopanda/wx

##  初始化
```
use saopanda\App;

App::new('appid','secret');
```

## 返回
业务成功 errcode = 0

result 为业务内容 json_decode() 后的数组
```
[
    'result'    =>  'array | false',
    'errmsg'    =>  'string | empty string',
    'errcode'   =>  int
]
```

## 方法
### 小程序登陆
```
App::login('code');
```

### 解密用户信息
```
//  前端返回加密信息
$data = [
    'iv'            =>  4
    'rawData'       =>  3,
    'signature'     =>  2,
    'encryptedData' =>  1,
]
App::getUserInfo($data,$session_key);
```

### 获取 accessToken
```
App::getAccessToken();
```

### 检查文字安全 同步
```
App::checkTextSync('文字');
```

### 待续







