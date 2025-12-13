<?php

namespace Opengerp\Modules\Users\Domain;

class Roles
{

    public static function caricaRuoli()
    {

        return array(
            0 => trans('amministratore'),
            1 => trans('operatore'),
            7 => trans('agente'),
            8 => trans('cliente'),
            9 => trans('fornitore'));

    }
}
