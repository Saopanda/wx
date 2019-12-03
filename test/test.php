<?php

    require_once '../vendor/autoload.php';

    use saoWx\saoBasic;
    $wx = new saoBasic('123123','niubi');

    echo $wx->getAppid();