<?php

namespace Opengerp\Utils\Files;

class Search
{

    public static function human_filesize($bytes, $decimals = 2)
    {
        $sz = 'BKMGTP';
        $factor = (int)floor((strlen($bytes) - 1) / 3);
        return sprintf("%.{$decimals}f", $bytes / pow(1024, $factor)) . @$sz[$factor];
    }


    public static function getExt($filename)
    {
        $vett = explode(".", $filename);

        return end($vett);
    }

    /**
     * @param $dir string
     * @param $ext ext pattern
     * @param $only_file boolean no recursion
     * @return array
     */
    public static function readDirRecursiveAsArray(string $dir, ?string $ext = null, bool $only_file = false) : array
    {

        $vett = [];

        if (!file_exists($dir)) {
            return $vett;
        }

        $d = dir($dir);
        if (!$d) {
            return $vett;
        }
        //echo "Handle: ".$d->handle."<br>\n";
        //echo "Path: ".$d->path."<br>\n";
        while (false !== ($entry = $d->read())) {

            if ($entry[0] != ".") {

                if (is_dir($dir . $entry) AND !$only_file) {

                    $vett2 = self::readDirRecursiveAsArray($dir . $entry . "/", $ext);
                    foreach ($vett2 as $k => $v) {
                        array_push($vett, $v);
                    }
                }

                if ($ext === null || self::getExt($entry) == $ext ) {

                    $data = date("Y-m-d H:i", filectime($dir . $entry));
                    $size = filesize($dir . $entry);

                    $vett[] = array(
                        'nome' => $dir . $entry,
                        'data' => $data,
                        'size' => $size,
                        'ext' => $ext,
                        'filename' => $entry,
                        'human_size' => self::human_filesize($size));
                }
            }

            //echo $entry."<br>\n";
        }
        // print_r($vett);
        sort($vett);
        return $vett;

    }


}