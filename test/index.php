<?php
    require_once '../vendor/autoload.php';

    use saowx\saoService;



//  小程序登录
//    $res =  $wx->sappLogin('021w2AXR0t51S52iocWR05UHXR0w2AXL');


//  小程序解密用户信息
//    $data['session_key'] = '';
//    $data['rawData'] = '';
//    $data['signature'] = "";
//    $data['encryptedData'] = '';
//    $data['iv'] = "";
//
//    $res = $wx->getUserInfo($data);


//  接收微信消息推送 服务器验证




//  发送小程序客服消息
//    $data = [
//        'text'=>[
//            'content' => 'niubi'
//        ]
//    ];
//    $touser = 'oDj8g5fDgz63Czh781r7iHqjwogI';
//    $res = $wx->sendMessage($touser,$data);
//    var_dump($res);

//  微信统一下单
    $data['appid'] = '';
    $data['secret'] = '';
    $data['mchid'] = '';
    $data['mchkey'] = '';
    $data['notify_url'] = '';
    $data['cert'] = '';
    $data['key'] = '/';
    $wx = new saoService($data);

    $data2['openid'] = '';
    $data2['amount'] = '100';
    $data2['partner_trade_no'] = 'a213123123123';
    $data2['desc'] = 'niubi';
    $rs = $wx->costToUser($data2);
    var_dump($rs);





