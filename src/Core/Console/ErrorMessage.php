<?php

namespace Opengerp\Core\Console;

class ErrorMessage extends Message
{


    public function display()
    {


        $str = $this->getStr();
        $msg = "<div class='alert alert-danger' role='alert'> <span class='alert_close'>&times;</span> $str </div>";

        echo $msg;


    }


}
