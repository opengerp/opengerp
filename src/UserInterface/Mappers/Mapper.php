<?php


namespace Opengerp\UserInterface\Mappers;

use ReflectionClass;

abstract class Mapper
{


    /**
     * Costruisce un array coerente con le richieste per l'elaborazione del preventivo,
     * inizializzato con valori default
     *
     * @return array
     */
    public function toArray(): array
    {
        $array = array();
        foreach ($this as $k => $v) {
            $array[$k] = $v;
        }
        return $array;
    }

    public function getConsolidateArray($vett_dati)
    {


        $vett_params = get_object_vars($this);

        foreach ($vett_params as $k => $v) {

            if (!isset($vett_dati[$k])) {
                $vett_dati[$k] = $v;

            }
        }

        return $vett_dati;


    }

    public function buildFromArray($vett_dati)
    {

        $reflection = new \ReflectionClass($this);

        foreach ($vett_dati as $k => $v) {

            if (!property_exists($this, $k)) {
                continue;
            }

            $prop = $reflection->getProperty($k);

            // Se la property è typed e ammette null -> passa null (tipicamente quando arriva '' da form)
            $type = $prop->getType();
            if ($type !== null && $type->allowsNull() && ($v === null || $v === '')) {
                $this->$k = null;
                continue;
            }

            // Cast base per typed properties (int/float/bool/string)
            if ($type instanceof \ReflectionNamedType) {
                $tname = $type->getName();

                if ($tname === 'int') {
                    $v = (int) $v;
                } elseif ($tname === 'float') {
                    $v = (float) $v;
                } elseif ($tname === 'bool') {
                    // gestisce "0","1","true","false","on","off"
                    $v = filter_var($v, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? false;
                } elseif ($tname === 'string' && $v !== null) {
                    $v = (string) $v;
                }
            }

            $this->$k = $v;
        }
    }


    /**
     * Popola il mapper a partire da un array DB
     */
    public function fromDbArray(array $data): self
    {

        //$data = $this->sanitize($data);

        foreach ($data as $key => $value) {
            // Se esiste una mappatura specifica nel mapper, usala
            $prop = $this->dbKeyMap()[$key] ?? $this->convertDbKeyToProperty($key);

            if (property_exists($this, $prop)) {
                $this->$prop = $value;
            }
        }

        return $this;
    }

    /**
     * Override nei mapper specifici per mappare chiavi non corrispondenti
     */
    protected function dbKeyMap(): array
    {
        return []; // default vuoto, mapper specifici possono sovrascrivere
    }

    /**
     * Converte chiavi tipo "Ragione_Sociale" o "ID" in "ragione_sociale" o "id"
     */
    protected function convertDbKeyToProperty(string $key): string
    {
        // Trasforma tutto in minuscolo
        $key = strtolower($key);

        // Sostituisce eventuali spazi o trattini con underscore
        $key = str_replace([' ', '-'], '_', $key);

        return $key;
    }


    protected function sanitize(array $data): array
    {
        $reflection = new \ReflectionClass($this);
        $sanitized = [];

        foreach ($data as $key => $value) {
            // Converti la chiave DB nella proprietà PHP corretta
            $propName = $this->dbKeyMap()[$key] ?? $this->convertDbKeyToProperty($key);

            if ($reflection->hasProperty($propName)) {
                $prop = $reflection->getProperty($propName);
                $type = $prop->getType();

                if ($value === null && $type !== null) {
                    $typeName = $type instanceof \ReflectionNamedType ? $type->getName() : null;
                    $allowsNull = $type->allowsNull();

                    // Se la proprietà non permette null, assegna valore di default
                    if (!$allowsNull) {
                        switch ($typeName) {
                            case 'string':
                                $value = '';
                                break;
                            case 'int':
                                $value = 0;
                                break;
                            case 'float':
                                $value = 0.0;
                                break;
                            case 'bool':
                                $value = false;
                                break;
                            default:
                                $value = null; // per oggetti o tipi sconosciuti
                        }
                    }
                }

                $sanitized[$propName] = $value;
            }
        }

        return $sanitized;


        //php8.3
        /*        $reflection = new \ReflectionClass($this);
                $sanitized = [];

                foreach ($data as $key => $value) {
                    // Converti la chiave DB nella proprietà PHP corretta
                    $propName = $this->dbKeyMap()[$key] ?? $this->convertDbKeyToProperty($key);

                    if (!$reflection->hasProperty($propName)) {
                        continue; // Salta chiavi non mappate
                    }

                    $prop = $reflection->getProperty($propName);
                    $type = $prop->getType();

                    if ($type instanceof \ReflectionNamedType && $value === null) {
                        // Se la proprietà non permette null, assegna valore di default
                        if (!$type->allowsNull()) {
                            $value = match ($type->getName()) {
                                'string' => '',
                                'int'    => 0,
                                'float'  => 0.0,
                                'bool'   => false,
                                default  => null, // oggetti o tipi sconosciuti
                            };
                        }
                    }

                    $sanitized[$propName] = $value;
                }

                return $sanitized;*/
    }


}
