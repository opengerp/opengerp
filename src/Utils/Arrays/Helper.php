<?php

namespace Opengerp\Utils\Arrays;

class Helper
{

    public static function addValue(&$array, $key, $value)
    {

        $value = estrai_numero($value);

        if ( ! isset($array[$key])) {
            $array[$key] = $value;
        } else {
            $array[$key] += $value;
        }
    }

    public static function concatString(&$array, $key, $string)
    {
        if ( ! isset($array[$key])) {
            $array[$key] = $string;
        } else {
            $array[$key] .= $string;
        }
    }

    /**
     * @param $array array
     * @return string
     */
    public static function keysToCsv($array)
    {
        if (!is_array($array)) {
            return '';
        }

        $vett_dati = array();
        foreach ($array as $k => $v) {
            $vett_dati[] = $k;
        }
        $str_dati = implode(",", $vett_dati);

        return $str_dati;

    }

    public static function toStringCsv($array)
    {
        if (!is_array($array)) {
            return '';
        }

        $vett_dati = array();
        foreach ($array as $k => $v) {
            $v = trim($v);
            if (strlen($v)) {
                $vett_dati[] = $v;
            }
        }

        $str_dati = implode("','", $vett_dati);

        return "'".$str_dati."'";

    }
    public static function csvToKeys($csv)
    {
        if (!$csv) {
            return array();
        }

        $vett2 = array();

        $vett = explode(",", $csv);
        foreach ($vett as $k) {
            $vett2[$k] = 1;
        }
        return $vett2;

    }
    public static function csvToArray($csv)
    {
        if (!$csv) {
            return array();
        }

        $vett2 = array();

        $vett = explode(",", $csv);
        foreach ($vett as $k) {
            $vett2[] = trim($k);
        }
        return $vett2;

    }

    public static function indexedAs($array, $index, $value_key=null)
    {

        $result = [];

        foreach($array as $line) {
            if ($value_key) {
                $result[$line[$index]] = $line[$value_key];

            } else {
                $result[$line[$index]] = $line;

            }
        }


        return $result;

    }


    public static function groupBy($array, $column_names, $columns_sum=[])
    {



        $rows = [];
        foreach($array as $row) {


            $key = '';

            foreach($column_names as $column_name) {
                $row[$column_name] = $row[$column_name] ?? '';

                $key .= $row[$column_name].'-';
            }


            if (!isset($rows[$key])) {

                $rows[$key] = $row;
            }


            if (!isset($rows[$key]['number'])) {

                $rows[$key]['number'] = 0;


            } else {


                foreach($columns_sum as $col) {
                    $rows[$key][$col] += $row[$col];
                }
            }


            $rows[$key]['number']++;

        }


        $results = [];
        foreach($rows as $row) {
            $results[] = $row;
        }

        return $results;

    }


    public static function utf8_converter($array)
    {
        array_walk_recursive($array, function(&$item, $key){
            if ( ! mb_detect_encoding($item, 'utf-8', true) ) {
                $item = utf8_encode($item);
            }
        });
        return $array;
    }


    /**
     * Converte un array associativo in una stringa formattata con un delimitatore specificato tra le coppie chiave-valore.
     *
     * @param array $array L'array associativo da convertire in stringa.
     * @param string $glue (optional) Il delimitatore da usare tra le coppie chiave-valore nella stringa risultante. Il valore predefinito è una virgola (',').
     * @param string $symbol (optional) Il simbolo da inserire tra la chiave e il valore. Il valore predefinito è il segno di uguale ('=').
     * @param string $pre_k (optional) La stringa da anteporre alla chiave. Il valore predefinito è una stringa vuota.
     * @param string $post_v (optional) La stringa da aggiungere al valore. Il valore predefinito è una stringa vuota.
     *
     * @return string La stringa formattata rappresentante l'array associativo.
     */
    public static function toString(array $array, string $glue = ',', string $symbol = '=', string $pre_k = '', string $post_v = ''): string
    {
        return implode($glue, array_map(
                function($k, $v) use($symbol,$pre_k,$post_v) {
                    return $pre_k.$k . $symbol . $v.$post_v;
                },
                array_keys($array),
                array_values($array)
            )
        );
    }


    /**
     * Raggruppa un array di elementi in base a una chiave e li restituisce ordinati.
     *
     * @param array $arr L'array di elementi da raggruppare.
     * @param string|int|float|callable $key La chiave da utilizzare per il raggruppamento (può essere una stringa, un numero, una funzione callable).
     * @param bool|null $asc Un valore booleano che indica l'ordinamento (true per crescente, false per decrescente). Se null, non viene effettuato l'ordinamento.
     *
     * @return array L'array raggruppato e ordinato (se richiesto).
     *
     * @throws \InvalidArgumentException Se la chiave non è una stringa, un numero, un float o una funzione callable.
     */
    public static function groupBySorted(array $arr, $key, $asc=true) : array
    {
        if (!is_string($key) && !is_int($key) && !is_float($key) && !is_callable($key)) {
            return $arr;
        }

        $isFunction = !is_string($key) && is_callable($key);

        // Load the new array, splitting by the target key
        $grouped = [];
        foreach ($arr as $value) {
            $groupKey = null;

            if ($isFunction) {
                $groupKey = $key($value);
            } else if (is_object($value)) {
                $groupKey = $value->{$key};
            } else {
                $groupKey = $value[$key];
            }

            $grouped[$groupKey][] = $value;
        }


        if ($asc===null) {
            return $grouped;
        }

        ksort($grouped);

        if (!$asc) {
            $grouped =  array_reverse($grouped);
        }

        return $grouped;
    }



    /**
     * Filtra un array in base a una chiave e un valore, applicando una condizione di confronto.
     *
     * Questo metodo filtra un array associativo o un array di oggetti in base a una chiave e un valore
     * specificati, utilizzando un operatore di confronto (default: `==`). È possibile scegliere se restituire
     * tutti gli elementi che soddisfano la condizione, il primo elemento oppure l'ultimo.
     *
     * @param array  $arr      L'array da filtrare (associativo o di oggetti).
     * @param string|int|float $key La chiave su cui eseguire il confronto. Può essere una stringa, un intero o un float.
     * @param string|int|float|array $value Il valore da confrontare con la chiave. Può essere una stringa, un intero o un float.
     * @param string $op       L'operatore di confronto. Il default è `==`, ma si possono aggiungere altri operatori (es. `!=`, `>`, `<`, etc.).
     * @param string $return   Specifica cosa restituire: `'all'` (default) per tutti gli elementi filtrati, `'first'` per il primo, `'last'` per l'ultimo.
     *
     * @return array|mixed Un array filtrato o un singolo elemento, a seconda del valore di `$return`.
     * - Se `$return` è `'first'`, restituisce il primo elemento che soddisfa la condizione.
     * - Se `$return` è `'last'`, restituisce l'ultimo elemento che soddisfa la condizione.
     * - Se `$return` è `'all'` (default), restituisce l'array con tutti gli elementi che soddisfano la condizione.
     */
    public static function filter(array $arr, $key, $value, string $op = '==', string $return = 'all'): array
    {
        if (!is_string($key) && !is_int($key) && !is_float($key)) {
            return $arr;
        }

        if (
            !is_string($value) &&
            !is_int($value) &&
            !is_float($value) &&
            !is_array($value)
        ) {
            return $arr;
        }

        $vett_filtered = array_filter($arr, function ($v, $k) use ($value, $key, $op) {
            $val = is_object($v) ? $v->{$key} ?? null : ($v[$key] ?? null);

            switch ($op) {
                case '<=':
                    return $val <= $value;
                case '<':
                    return $val < $value;
                case '>=':
                    return $val >= $value;
                case '>':
                    return $val > $value;
                case '!=':
                    return $val != $value;
                case 'in':
                    return is_array($value) && in_array($val, $value);
                case '!in':
                    return is_array($value) && !in_array($val, $value);
                default: // '=='
                    return $val == $value;
            }
        }, ARRAY_FILTER_USE_BOTH);

        if ($return === 'first') {
            return array_shift($vett_filtered) ?: [];
        } elseif ($return === 'last') {
            return array_pop($vett_filtered) ?: [];
        }

        return $vett_filtered ?: [];
    }


    /**
     * Filtra un array convertendo i valori validi in numeri decimali (float).
     *
     * Per ogni elemento dell'array:
     * - Se il valore è un numero decimale valido, viene convertito in float.
     * - Altrimenti, viene restituito così com'è.
     *
     * @param array|null $array L'array di input da filtrare.
     * @return array L'array con i valori decimali convertiti.
     */
    public static function filterDecimal(?array $array): array
    {

        if ($array == null) {
            return [];
        }

        return array_map(function ($value) {
            // Controlla se il valore è un float valido e non è una stringa vuota
            if (
                // $value !== '' &&
                // $value !== null &&
                $value !== false &&
                ! is_array($value) &&
                \Opengerp\Utils\Strings\Filters::isValidFloat($value)
            ) {
                return \Opengerp\Utils\Strings\Filters::filterDecimal($value);
            }

            return $value;
        }, $array);
    }

}
