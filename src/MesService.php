<?php

namespace saowx;

class MesService
{
    private static $mes;
    protected $messageToken,$messageKey;

    protected function __construct($messageToken,$messageKey)
    {
        $this->messageToken = $messageToken;
        $this->messageKey = $messageKey;
    }

    static function new($messageToken,$messageKey)
    {
        if (is_null(self::$mes)){
            self::$mes = new self($messageToken,$messageKey);
        }
        return self::$mes;
    }

    //  发送消息
    public function send()
    {
        return $this;
    }

    //  下发输入状态
    protected function setTyping()
    {

    }

}
