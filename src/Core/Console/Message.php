<?php

namespace Opengerp\Core\Console;

abstract class Message
{

    protected $str;
    protected $params = [];


    public function __construct($str, $params)
    {
        if ( ! is_array($str) ) {
            $str = trans($str, $params);
            $str = vsprintf($str, $params);
        }

        $this->str = $str;
        $this->params = $params;
    }



    public function display()
    {
        $str = $this->str;
        $msg = "<div class='alert alert-info' role='alert'> <span class='alert_close'>&times;</span> $str </div>";

        echo $msg;
    }



    public function getStr()
    {
        return $this->str;
    }

}
