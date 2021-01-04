<?php

require_once '../vendor/autoload.php';

use saopanda\App;
use saopanda\Pay;

$app = App::new('1','2',123,123);

$res = App::login('123');
var_dump($res);

$res = App::getUserInfo(['rawData'=>1,'signature'=>2,'encryptedData'=>3,'iv'=>4],'qqq');
var_dump($res);

$res = App::getAccessToken();
var_dump($res);

$res = App::checkTextSync('啊啊啊');
var_dump($res);

$pay = Pay::new('1','1','1',
'1');

$res = Pay::order('JSAPI','1','商品','111','asd');
var_dump($res);



