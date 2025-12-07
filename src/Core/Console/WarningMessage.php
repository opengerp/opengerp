<?php

namespace Opengerp\Core\Console;

class WarningMessage extends Message
{


    public function display()
    {
        $str = $this->str;
        $msg = "<div class='alert alert-warning' role='alert'> <span class='alert_close'>&times;</span> $str </div>";

        echo $msg;
    }


}