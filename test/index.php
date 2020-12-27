<?php

require_once '../vendor/autoload.php';

use saopanda\App;

$app = App::new(123,123,123,123);

$res = $app->login('123');
var_dump($res);

$res = $app->getUserInfo(['rawData'=>1,2,3,4],'qqq');
var_dump($res);
