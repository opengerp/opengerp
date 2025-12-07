<?php

namespace Opengerp\System;

class MenuItems
{

    public static function checkTableMenu($file_moduli)
    {


        $obj_schema = simplexml_load_file($file_moduli);

        if (!file_exists($file_moduli)) {

            \Opengerp\Core\Console\Console::appendError("File menu %s non corretto", [$file_moduli]);
            return false;

        }

        foreach ($obj_schema->children() as $module) {

            $traduzioni = array();
            foreach ($module->Json_Traduzione->children() as $trad) {

                $a = (array) json_decode(json_encode($trad->attributes()->lang), true);
                $b = (array) json_decode(json_encode($trad), true);

                $cod = $a[0];
                if (isset($b[0])) {


                    $traduz = $b[0];
                    $traduzioni = $traduzioni + array($cod => $traduz);
                }
            }

            $json = json_encode($traduzioni);

            $dbo = new \Opengerp\Core\DbObjects\MenuItems();
            $dbo->ID_Menu = (string) $module->ID_Menu;
            $dbo->Voce_Menu = (string) $module->Voce_Menu;
            $dbo->Icon = (string) $module->Icon;
            $dbo->Json_Traduzione = $json;

            $dbo->upsert();

        }

    }

}
