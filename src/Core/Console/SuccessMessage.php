<?php

namespace Opengerp\Core\Console;

class SuccessMessage extends Message
{


    public function display()
    {
        $str = $this->getStr();
        $msg = "<div class='alert alert-success' role='alert'> <span class='alert_close'>&times;</span> $str </div>";

        echo $msg;
    }


}