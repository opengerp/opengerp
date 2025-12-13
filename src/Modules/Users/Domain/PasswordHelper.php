<?php

namespace Opengerp\Modules\Users\Domain;

class PasswordHelper
{

    public static function randomPassword(int $length = 8)
    {

        $chars = array_merge(range('0', '9'), range('A', 'Z'), range('a', 'z'));

        $pass = [];
        for ($i = 0; $i < $length; $i++) {
            $n = rand(0, (count($chars) - 1));
            $pass[] = $chars[$n];
        }
        return implode($pass);
    }

    public static function gerp_make_password($password_length = 12)
    {
        //$salt = "0123456789";

        $pass = '';

        srand((double)microtime() * 1000000);
        $i = 0;
        while ($i <= $password_length) {
            $num = rand(1, 9);
            $tmp = $num;
            $pass = $pass . $tmp;
            $i++;
        }

        return $pass;
    }


}
