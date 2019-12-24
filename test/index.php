<?php
    require_once '../vendor/autoload.php';

    use saowx\saoService;

    $wx = new saoService('','');


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
    $data = [
        'text'=>[
            'content' => 'niubi'
        ]
    ];
    $touser = 'oDj8g5fDgz63Czh781r7iHqjwogI';
    $res = $wx->sendMessage($touser,$data);
    var_dump($res);






