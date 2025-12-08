<?php

namespace Opengerp\Core\Console;

abstract class Message
{

    protected string $str;
    protected array $context = [];


    public function __construct($str, $params)
    {
        $this->str = $str;
        $this->context = $params;
    }



    public function display()
    {
        $str = $this->str;
        $msg = "<div class='alert alert-info' role='alert'> <span class='alert_close'>&times;</span> $str </div>";

        echo $msg;
    }



    public function getStr()
    {
        if (function_exists('trans')) {
            return trans($this->str, $this->context);
        }
        return vsprintf($this->str, $this->context);

    }

    public function __toString()
    {
        return $this->getStr();
    }
}
