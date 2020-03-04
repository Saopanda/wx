<?php
    require_once '../vendor/autoload.php';

    use saowx\SaoService;


    $app = SaoService::app(
        '111',
        '111'
        );

#   小程序登陆
//    $res = $app->login('081lgZd51GGY8R1ZsYf51Re0e51lgZdg');

#   小程序用户信息解密
//    $sessionkey = '#####+GAZtO3gfw==';
//
//    $res = $app->getUserInfo(
//        '{####"}',
//        '4ae16#8668f',
//        'W/d######=',
//        '5N########vcXQ==',
//        $sessionkey
//    );

#   小程序图片检查 同步、异步
//    $rs = 'http://com/888.jpg-cut';
//    $rs2 = './666.jpg';

//    $res = $app->checkPicture($rs2);
//    $res = $app->checkMedia($rs);

#   小程序文字检测 同步
//    $res = $app->checkText('123123');


#   获取小程序码
//    $m = [
//        'width'=>'1111',
//        'auto_color'=>true
//    ];
//    $res = $app->getQRcode('xinxi',$m);
//    header('Content-Type: image/png');
//    echo $res->data;

#   微信支付统一下单
    $pay = SaoService::pay(
        '111',
        '111',
        '111',
        '666.com'
    );

//    $rs = $pay->order(
//        'ozOFO5YT26As460BBuo40-riDdZg',
//        '222',
//        '100',
//        'efqdqwd'
//    );

    $rs = $pay->costToUser(
        '',
        '',
        '',
        '',
    );

    var_dump($rs);

//    $res = $app->test();
//
//
//
//    var_dump($res);

//    $pay = SaoService::pay($data);
//
//    $app->
//    $pay->objectToArray(1);


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
//    $data['appid'] = '';
//    $data['secret'] = '';
//    $data['mchid'] = '';
//    $data['mchkey'] = '';
//    $data['notify_url'] = '';
//    $data['cert'] = '';
//    $data['key'] = '/';
//    $wx = new saoService($data);
//
//    $data2['openid'] = '';
//    $data2['amount'] = '100';
//    $data2['partner_trade_no'] = 'a213123123123';
//    $data2['desc'] = 'niubi';
//    $rs = $wx->costToUser($data2);
//    var_dump($rs);





