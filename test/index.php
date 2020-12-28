<?php

require_once '../vendor/autoload.php';

use saopanda\App;

$app = App::new(123,123,123,123);

$res = App::login('123');
var_dump($res);

$res = App::getUserInfo(['rawData'=>1,'signature'=>2,'encryptedData'=>3,'iv'=>4],'qqq');
var_dump($res);


