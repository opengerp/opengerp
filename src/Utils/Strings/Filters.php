<?php

namespace Opengerp\Utils\Strings;

use DateTime;

class Filters
{

    public static function isValidUsername($string)
    {

        if (!preg_match('/^[a-zA-Z0-9\/\-\_:@.]+$/', $string)) {
            // echo $_GET[$campo];
            return false;
        }


        return true;

    }

    public static function isValidCode($string)
    {

        if (is_array($string)) {
            throw  new \Exception();
        }

        if (!preg_match('/^[a-zA-Z0-9\/\-\_:.]+$/', $string)) {
            // echo $_GET[$campo];
            return false;
        }


        return true;

    }


    /**
     * Verifica se la stringa fornita rappresenta un numero valido in vari formati.
     *
     * Questa funzione supporta i seguenti formati:
     * - Numeri interi (ad esempio, "123", "-123", "+123")
     * - Numeri decimali con separatori di migliaia opzionali (ad esempio, "1.000", "1.000.000")
     * - Numeri con punto decimale (ad esempio, "123.456", "1.234,56")
     * - Notazione esponenziale (ad esempio, "1.23E4", "123.456e-3")
     * - Il separatore decimale può essere un punto (.) o una virgola (,), ma la virgola può essere usata solo per i decimali.
     *
     * Il pattern è progettato per validare e gestire:
     * - Numeri positivi e negativi.
     * - Notazione esponenziale (`e` o `E`).
     * - Separatori di migliaia utilizzando un punto (`.`) per migliorare la leggibilità.
     * - Punti decimali usando un punto (`.`) o una virgola (`,`), ma non entrambi nello stesso numero.
     *
     * Questa funzione non consente la miscelazione del separatore delle migliaia e del separatore decimale (ad esempio, `1,000.00` non è valido).
     *
     * Esempi di input validi:
     * - "123"
     * - "1.000" (con punto come separatore delle migliaia)
     * - "1000,50" (con virgola come separatore decimale)
     * - "1.234,56" (con virgola come separatore decimale)
     * - "123.456e3" (notazione esponenziale)
     * - "1.23E4" (notazione esponenziale)
     *
     * Esempi di input non validi:
     * - "1,000.00" (miscelazione del separatore delle migliaia e del punto decimale)
     * - "1.234,567" (uso errato della virgola nei decimali)
     *
     * @param string $string La stringa contenente il numero da validare.
     *
     * @return bool Ritorna `true` se la stringa corrisponde a un formato numerico valido, altrimenti ritorna `false`.
     */
    public static function isValidFloat($string) : bool
    {
        // Se la variabile è un oggetto, non è un valore valido
        if (is_object($string)) {
            return false;
        }

        // 0 is intended
        if ($string === null) {

            return true;
        }

        $string = trim($string);


        // 0 is intended
        if ($string === '') {
            return true;
        }


        // Blocca il formato anglosassone (es: "1,234.56")
        if (preg_match('/,.*\./', $string)) {
            return false;
        }

        $pattern = '/^[+-]?\d{1,3}(\.\d{3})*(,\d+)?$|^[+-]?\d+(.\d+)?$/';
        $pattern = '/^[+-]?\d{1,3}(?:\.\d{3})*(?:,\d+)?$|^[+-]?\d+(?:,\d+)?(?:\.\d+)?([eE][+-]?\d+)?$/';


        if (!preg_match($pattern, $string)) {

            return false;
        }


        return true;

    }

    public static function extractDecimal($stringa)
    {
        // Cerca il primo numero decimale nella stringa
        if (preg_match('/[-+]?[0-9]*[.,]?[0-9]+/', $stringa, $match)) {
            // Sostituisce la virgola con il punto per uniformità
            $numero = str_replace(',', '.', $match[0]);
            return floatval($numero);
        }
        return null; // Nessun numero trovato
    }

    public static function isValidPositiveFloat($string)
    {

        if (!preg_match('/^[0-9.]+$/', $string)) {
            // echo $_GET[$campo];
            return false;
        }


        return true;

    }

    public static function isValidInt($string)
    {

        if (!preg_match('/^[0-9]+$/', $string)) {
            // echo $_GET[$campo];
            return false;
        }


        return true;
    }


    public static function filterValidCode($string)
    {
        try {
            if (self::isValidCode($string)) {
                return $string;
            }
        } catch (\Exception $e) {
            \Gerp\System\ErrorLog::appendError(__METHOD__ . ' > '. $e->getMessage(), 'FILTER', debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT,2));
        }

        return '';


    }
    public static function isInt($string)
    {
        if (filter_var($string, FILTER_VALIDATE_INT) === false) {
            return false;

        }

        return true;


    }

    public static function filterInt($string) : int
    {



        if ( ! function_exists('filter_input')) {

            if (!$string) {
                return 0;
            }

            return ctype_digit(strval($string));


        }


        if ( ! filter_var($string, FILTER_VALIDATE_INT)) {
            return 0;

        }


        return filter_var($string, FILTER_SANITIZE_NUMBER_INT);



    }

    public static function filterFloat($string) : float
    {

        if ($string === null) {
            return 0;
        }

        $string = trim($string);

        if (strlen($string) == 0) {
            return 0;
        }

        return (float) (str_replace(",", ".", $string));



    }
    public static function getFirstGroupOfIntegers($string)
    {
        preg_match('/^[^\d]*(\d+)/', $string, $matches);

        return $matches[1];
    }

    public static function removeNonIntegerChars($string)
    {
        $res = preg_replace("/[^0-9]/", "", $string);

        $res  = intval($res);

        $res = self::filterInt($res);



        return $res;


    }

    /**
     * Filtra una stringa e restituisce il valore di un numero in formato float.
     * La funzione supporta sia il punto che la virgola come separatori decimali.
     * Se la stringa è vuota, `null`, o non rappresenta un numero valido, la funzione restituisce 0.
     *
     * @param string $string La stringa da filtrare e convertire in un numero di tipo float.
     *
     * @return float Il valore del numero convertito in virgola mobile (float).
     */
    public static function filterDecimal($string) : float
    {

        if ($string === null || $string === '') {
            return 0;
        }

        /*
        if ($string === null) {
            $string = '';
        }

        if ( !preg_match( '#[0-9]+(\,|\.)[0-9]+#', $string ) && !preg_match( '#[0-9]+#', $string ) ) {
            return 0;
        }
        */

        if (!self::isValidFloat($string)) {
            return 0;
        }

        if ( preg_match( '#\,#' , $string )) {
            // Presumiamo formato europeo: 1.234,56
            // Rimuovi eventuali punti (per evitare numeri con separatori di migliaia)
            $string = preg_replace('#\.#', '' , $string);

            $string = preg_replace('#\,#', '.' , $string);

        } elseif (preg_match_all('/[.]/', $string, $matches) > 1) {

            // Rimuovi eventuali punti (per evitare numeri con separatori di migliaia)
            $string = preg_replace('#\.#', '' , $string);
        }

        return (float) $string;

    }

    public static function string2friendly_url($string)
    {

        $string = preg_replace("`\[.*\]`U", "", $string);
        $string = preg_replace('`&(amp;)?#?[a-z0-9]+;`i', '-', $string);
        $string = htmlentities($string, ENT_COMPAT, 'utf-8');
        $string = preg_replace("`&([a-z])(acute|uml|circ|grave|ring|cedil|slash|tilde|caron|lig|quot|rsquo);`i", "\\1", $string);
        $string = preg_replace(array("`[^a-z0-9]`i", "`[-]+`"), "-", $string);

        return strtolower(trim($string, '-'));
    }

    /**
     * @param $url string
     * @return string
     */
    public static function filterUrl($url)
    {

        if (!$url) {
            return '';
        }

        if (substr($url, 0, 7) != 'http://' && substr($url, 0, 8) != 'https://') {
            $url = 'http://'.$url;
        }

        if (preg_match('/@/', $url)) {
            return '';
        }

        if (!filter_var($url, FILTER_VALIDATE_URL, FILTER_FLAG_HOST_REQUIRED)) {

            return '';
        }

        $vett_url = parse_url($url);
        $myDomainName = $vett_url['host'];

        if (gethostbyname($myDomainName) == $vett_url['host']) {
            return '';
        }

        return $url;
    }

    public static function isDateDaysMonthYear($str_data)
    {

        // controlla che la stringa sia una data in un formato valido
        // gg/mm/aa

        // gg/mm/aaaa

        $vett = explode("/", $str_data);

        if (count($vett)!=3)  {
            return false;
        }

        if (!is_numeric($vett[0])) {
            return false;
        }
        if (!is_numeric($vett[1])) {
            return false;
        }

        if (!is_numeric($vett[2])) {
            return false;
        }

        if ($vett[0]>31 || $vett[0]<1) {
            return false;
        }

        if ($vett[1]>12 || $vett[1]<1) {
            return false;
        }

        if ( (strlen($vett[2])!= 4 && strlen($vett[2])!=2 ) ) {

            return false;
        }


        return true;



    }

    /**
     * The function lets to check whether a given datetime string is a valid datetime and has one of the specified formats.
     *
     * Iterates through the provided formats, attempting to parse the input string.
     * Returns the first successful match that strictly adheres to the format.
     *
     * var_dump(filterDateTime('2012-02-28', ['Y-m-d'])); # DateTime
     * var_dump(filterDateTime('28/02/2012', ['d/m/Y'])); # DateTime
     * var_dump(filterDateTime('30/02/2012', ['d/m/Y'])); # null
     * var_dump(filterDateTime('14:50', ['H:i'])); # DateTime
     * var_dump(filterDateTime('14:77', ['H:i'])); # null
     * var_dump(filterDateTime(14, ['H'])); # DateTime
     * var_dump(filterDateTime('14', ['H'])); # DateTime
     *
     * @param string $string The datetime string to parse.
     * @param string[] $formats An array of date/time format strings (e.g., ['Y-m-d H:i:s', 'd/m/Y']).
     * @return DateTime|null A DateTime object representing the parsed string if successful, or null otherwise.
     */
    public static function filterDateTime(string $string, array $formats): ?DateTime
    {
        foreach ($formats as $format) {

            $datetime = DateTime::createFromFormat($format, $string);

            if ($datetime && $datetime->format($format) == $string) {
                return $datetime;
            }
        }

        return null;
    }

    /**
     * Validates if a given string is a properly formatted hexadecimal color code.
     *
     * This function checks that the input string starts with a '#' character, is precisely 7 characters long,
     * and contains only valid hexadecimal digits (0-9, A-F, a-f) after the '#' character.
     *
     * @param string $code The input string to validate as a hexadecimal color code.
     * @return string|null Returns the valid hexadecimal color code if valid, or null if the validation fails.
     */
    public static function filterColorCode(string $code): ?string
    {

        $code = trim($code);

        if (strpos($code, '#') !== 0) {
            return null;
        }

        if (strlen($code) != 7) {
            return null;
        }

        $hex = str_replace('#', '', $code);

        if ( ! ctype_xdigit($hex)) {
            return null;
        }

        return $code;
    }

}
