<?php

namespace Opengerp\Core\Http;

class Url
{
    public static function buildLink($des, $vett, $class = "", $confirm = false, $target = false, $rel = false, $download = '', string $title = '')
    {
        $url = self::buildUrl($vett);

        $final = "<a href='$url' class='$class'";

        if ($confirm) {
            $encoded = is_bool($confirm) ? "" : htmlspecialchars(json_encode($confirm));
            $final .= " onclick='if (!gerp_confirm($encoded)) return false;'";
        }

        if ($target) {
            $final .= " target='$target' ";
        }

        if ($rel) {
            $final .= " rel='$rel' ";
        }

        if ($download != '') {
            $final .= ' download="' . $download . '"';
        }

        $final .= ' title="' . $title . '"';

        $final .= ">$des</a>";

        return $final;
    }

    /**
     * @param string $des
     * @param array $vett
     * @return string
     */
    public static function buildLinkWithModal(string $des = '', array $vett = [], $class = ''): string
    {
        $url = self::buildLink($des, $vett, $class, false, false, 'modal:open');

        return $url;
    }

    public static function buildUrl($vett, $add_base = true)
    {
        $base = "";

        if ($add_base) {
            $base = 'index.php?';
        }

        $base .= http_build_query($vett);


        return $base;

    }



}
