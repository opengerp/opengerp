<?php

namespace Opengerp\Database;

use Opengerp\Database\Column;
use Opengerp\Utils\Strings\Filters;

abstract class DbObject
{


    const TABLE_NAME = null;

    const TABLE_ENTITY = null;

    const TABLE_PRIMARY_KEY = null;

    const TABLE_ENTITY_FOREIGN_KEY = null;


    /**
     * @var Column[]
     */
    protected $_columns = [];

    /**
     * @var $original []
     */
    protected $original = [];

    /**
     * @var $log
     */
    protected $log;

    protected static ?Db $defaultDb = null;

    protected Db $db;


    public static function setDefaultDb(Db $db): void
    {
        self::$defaultDb = $db;
    }

    public static function getDefaultDb(): ?Db
    {
        return self::$defaultDb;
    }


    /**
     * @todo non differenzia valori INT da STRING
     * @param $array
     */
    public function setFromArray($array)
    {
        foreach ($array as $k => $v) {

            if (property_exists($this, $k)) {


                $this->$k = $v;


                if (isset($this->_columns[$k]) && $this->_columns[$k]->type == Column::TYPE_JSON) {

                    if (is_null($v)) {
                        $v = '';
                    }
                    $this->_columns[$k]->decoded_json = (array) json_decode($v, true);
                }


            }
        }


    }

    public function setJsonParam($json_column, $param, $value)
    {


        if (!isset($this->_columns[$json_column])) {

            throw new \Exception();
        }


        $this->_columns[$json_column]->decoded_json[$param] = $value;

        $this->$json_column = json_encode($this->_columns[$json_column]->decoded_json);


    }

    public function getJsonParam($json_column, $param, $default = null)
    {

        if (!isset($this->_columns[$json_column])) {

            return null;
        }


        if ($this->_columns[$json_column]->type != Column::TYPE_JSON) {

            throw new \Exception('Colonna json non impostata');
        }


        if (!isset($this->_columns[$json_column]->decoded_json[$param])) {
            return $default;
        }



        return $this->_columns[$json_column]->decoded_json[$param];

    }


    public function toArray()
    {
        $result = [];
        foreach ($this as $k => $v) {

            if (in_array($k, ['_columns', 'original', 'auth', 'log'])) {

                continue;
            }

            $result[$k] = $v;


        }

        return $result;

    }

    private function isPrimaryKeyAutoincrement($name)
    {
        if (!isset($this->_columns[$name])) {
            return false;
        }

        if ($this->_columns[$name]->autoincrement) {
            return true;
        }

        return false;
    }



    public function buildInsertQuery()
    {


        $db = $this->getDb();
        $table_name = $this->getTableName();

        $query = "INSERT INTO $table_name ( ";

        $vett_columns = [];
        $vett_values = [];

        foreach ($this as $k => $v) {

            if (in_array($k, ['_columns', 'original', 'auth', 'log'])) {

                continue;
            }

            if ($this->isPrimaryKeyAutoincrement($k)) {
                continue;

            }



            $vett_columns[] = $k;


            if (isset($this->_columns[$k]) && $v === null && $this->_columns[$k]->null) {

                $vett_values[] = 'NULL';

            } else {


                if (isset($this->_columns[$k]) && $this->_columns[$k]->type == Column::TYPE_INT) {


                    $v = Filters::filterInt($v);


                }

                if (isset($this->_columns[$k]) && in_array($this->_columns[$k]->type, [Column::TYPE_DECIMAL, Column::TYPE_DOUBLE, Column::TYPE_FLOAT])) {
                    $v = Filters::filterDecimal($v);
                }




                if (isset($this->_columns[$k])
                    && $this->_columns[$k]->type == Column::TYPE_DATETIME
                    && $this->_columns[$k]->default == Column::DEFAULT_NOW
                    && !$v)
                {
                    $v = date('Y-m-d H:i:s');
                }

                if (isset($this->_columns[$k])
                    && $this->_columns[$k]->type == Column::TYPE_DATE
                    && $this->_columns[$k]->default == Column::DEFAULT_NOW
                    && !$v)
                {
                    $v = date('Y-m-d');
                }

                if (isset($this->_columns[$k])
                    && $this->_columns[$k]->type == Column::TYPE_DATETIME
                    && $this->_columns[$k]->null
                    && $v === '')  {

                    $vett_values[] = 'NULL';

                } else {

                    $v = $db->escape_string($v);
                    $vett_values[] = "'" . $v . "'";

                }



            }


        }

        $query .= implode(',', $vett_columns);

        $query .= ') VALUES (';

        $query .= implode(',', $vett_values);

        $query .= ' ) ';

        return $query;

    }



    public function all()
    {
        $table_name = $this->getTableName();


        $db = $this->getDb();

        $ris = $db->query("SELECT * FROM $table_name");

        return $ris->fetchAll();
    }


    public function getDb()
    {


        return self::$defaultDb;


    }

    public function insert($simulate = false)
    {
        $query = $this->buildInsertQuery();

        $primary_keys = get_called_class()::TABLE_PRIMARY_KEY;
        $vett_primary_keys = explode(',', $primary_keys);
        $primary_key = $vett_primary_keys[0];


        $db = $this->getDb();

        if ( $simulate ) {
            gerp_display_log($query);
            return false;
        }


        if ( ! $response = $db->query($query) ) {
            gerp_display_log("<b>" . gsql_error() . " (". $this::TABLE_NAME .")</b>");
        }


        if ( $this->isPrimaryKeyAutoincrement($primary_key) ) {
            $this->$primary_key = $db->lastInsertId();
        }


        // TODO: definire la costante TABLE_ENTITY_FOREIGN_KEY in tutti i DbObjects con un'entità
        if ($response && $this->log) {
            $operation_code = get_class($this) . '::insert';

            if ( ! $relation_id = get_called_class()::TABLE_ENTITY_FOREIGN_KEY) {
                $relation_id = $this->getPrimaryKey();
            }

            $relation = [
                'id' => $this->{$relation_id},
                'name' => get_called_class()::TABLE_ENTITY
            ];

            $vett_ref = [];

            foreach($vett_primary_keys as $p_key) {
                $vett_ref[] = $this->$p_key;
            }

            $ref = $vett_ref[0];
            $ref2 = $vett_ref[1] ?? '';

            $this->auth->salvaLog($operation_code, $ref, $this->getAttributesToBeLoggedOnInsert(), $relation, $ref2);
        }

        return $response;
    }


    /**
     * @param string|null ...$ids
     * @return bool
     * @throws \Exception
     */
    public function loadById(?string ...$ids)
    {
        $c = get_called_class();
        $primarys = explode(',', $c::TABLE_PRIMARY_KEY);



        $c = 0;
        $vett_primary_keys = [];

        foreach($primarys as $primary) {

            if ($ids[$c] == null) {
                $vett_primary_keys[$primary] = $this->$primary;
            } else {
                $vett_primary_keys[$primary] = $ids[$c];
            }
        }


        return $this->loadBy($vett_primary_keys);

    }


    /**
     * attenzione questo metodo restituisce solo la prima occorrenza, andrebbe sostituito con restituzione di una collection
     * @param array $data
     * @return bool
     * @throws \Exception
     */
    public function loadBy(array $data) : bool
    {
        $table_name = $this->getTableName();

        if (!$table_name) {
            throw new \Exception(get_class($this)." tabella non definita correttamente");

        }
        $query = "SELECT * FROM $table_name WHERE ";

        $keys = array_keys($data);

        $lastkey = end($keys);

        foreach ($data as $k => $v) {

            if ($this->_columns[$k]->type == Column::TYPE_INT ||
                $this->_columns[$k]->type == Column::TYPE_DOUBLE ||
                $this->_columns[$k]->type == Column::TYPE_FLOAT) {

                $query .= $k . " = " . $v;

            } else {

                $query .= $k . " = '" . $v . "'";

            }

            if ($k != $lastkey) {
                $query .= " AND ";
            }

        }


        $db = $this->getDb();

        $ris = $db->query($query) or dd($query);

        if ($lin = $ris->fetch()) {
            $this->original = $lin;

            $this->setFromArray($lin);

            return true;
        }

        return false;

    }


    public function update($simulate = false)
    {

        $table_name = $this->getTableName();

        $primaryKeys = $this->getPrimaryKeys();

        $tokens = [];

        foreach ($this as $k => $v) {

            if (in_array($k, ['_columns', 'original', 'auth', 'log'])) {

                continue;
            }

            if ($this->isPrimaryKey($k)) {
                continue;

            }


            // integers stays zero


            if (isset($this->_columns[$k]) && $this->_columns[$k]->type == Column::TYPE_INT) {

                if ($this->_columns[$k]->null && $v === null) {
                    $v = null;
                } else {
                    $v = Filters::filterInt($v);
                }

            }

            if (isset($this->_columns[$k]) && in_array($this->_columns[$k]->type, [Column::TYPE_DECIMAL, Column::TYPE_DOUBLE, Column::TYPE_FLOAT])) {

                $v = Filters::filterDecimal($v);
            }

            if (isset($this->_columns[$k]) && $this->_columns[$k]->type == Column::TYPE_DATETIME && $this->_columns[$k]->null) {

                if (!$v) {
                    $v = null;
                }

            }



            if (isset($this->_columns[$k]) && $this->_columns[$k]->type == Column::TYPE_DATE && $this->_columns[$k]->null) {

                if (!$v) {
                    $v = null;
                }

            }



            if (isset($this->_columns[$k]) && $v === null && $this->_columns[$k]->null) {

                $tokens[] = 'NULL';

            } else {

                $v = $this->db->escape_string($v);
                $tokens[] = "'" . $v . "'";

            }

        }


        $query = "UPDATE $table_name SET ";

        $i = 0;
        foreach ($this as $k => $v) {
            if ($i == 0) {
                $i++;
                continue;
            }

            if (!in_array($k, ['_columns', 'original', 'auth', 'log']) and !$this->isPrimaryKey($k)) {
                $query .= "$k = " . $tokens[$i - 1] . ", ";
                $i++;
            }
        }

        $query = rtrim($query, ", ");

        $query .= " WHERE ";

        foreach ($primaryKeys as $p => $v) {

            if ( ! $v && $v !== "0" ) {
                throw new \Exception("Primary key $p without value");
            }

            $value = $this->$p;
            $query .= "$p = '$value' AND ";
        }

        $query = rtrim($query, "AND ");
        $query .= " LIMIT 1";

        if ($simulate) {
            echo ($query);
            return true;

        }


        $db = $this->getDb();

        $response = $db->query($query);


        if ($response && $this->log && $this->isRealUpdate()) {

            $this->saveUpdateLog($this->getAttributesToBeLoggedOnUpdate());
        }

        return $response;


    }

    public function saveUpdateLog($json_str_update)
    {

        $operation_code = get_class($this) . '::update';

        $primary_keys = get_called_class()::TABLE_PRIMARY_KEY;
        $vett_primary_keys = explode(',', $primary_keys);
        $primary_key = $vett_primary_keys[0];

        if ( ! $relation_id = get_called_class()::TABLE_ENTITY_FOREIGN_KEY) {
            $relation_id = $primary_key;
        }

        $relation = [
            'id' => $this->{$relation_id},
            'name' => get_called_class()::TABLE_ENTITY
        ];

        $vett_ref = [];

        foreach($vett_primary_keys as $p_key) {
            $vett_ref[] = $this->$p_key;
        }

        $ref = $vett_ref[0];
        $ref2 = $vett_ref[1] ?? '';

        $this->auth->salvaLog($operation_code, $ref, $json_str_update, $relation, $ref2);

    }

    public function getPrimaryKey()
    {
        $c = get_called_class();
        return $c::TABLE_PRIMARY_KEY;
    }

    public function getPrimaryKeys()
    {
        $primaryKeys = array();

        $c = get_called_class();
        if (null !== $c::TABLE_PRIMARY_KEY) {

            $vett = explode(',', $c::TABLE_PRIMARY_KEY);

            foreach($vett as $v) {
                $primaryKeys[$v] = $this->$v;
            }


        } else {
            foreach ($this->_columns as $k => $v) {
                if ($v->primary_key and gettype($v->primary_key) == "boolean") {
                    $primaryKeys[$k] = $v;
                }
            }
        }
        return $primaryKeys;
    }

    public function getPrimaryKeysValue()
    {
        $vett = $this->getPrimaryKeys();


    }

    public function delete()
    {

        $table_name = $this->getTableName();

        $primaryKeys = $this->getPrimaryKeys();

        $query = "DELETE FROM $table_name WHERE ";


        foreach ($primaryKeys as $p => $v) {
            $value = $this->$p;
            $query .= "$p = '$value' AND ";
        }

        $query = rtrim($query, "AND ");
        $query .= " LIMIT 1";

        $db = $this->getDb();

        $response = $db->query($query);

        if ($response && $this->log) {

            $operation_code = get_class($this) . '::delete';


            $primary_key = explode(',', $this->getPrimaryKey())[0];

            if ( ! $relation_id = get_called_class()::TABLE_ENTITY_FOREIGN_KEY) {
                $relation_id = $primary_key;
            }

            $relation = [
                'id' => $this->{$relation_id},
                'name' => get_called_class()::TABLE_ENTITY
            ];


            $primary_keys = get_called_class()::TABLE_PRIMARY_KEY;
            $vett_primary_keys = explode(',', $primary_keys);

            $vett_ref = [];

            foreach($vett_primary_keys as $p_key) {
                $vett_ref[] = $this->$p_key;
            }

            $ref = $vett_ref[0];
            $ref2 = $vett_ref[1] ?? '';



            $this->auth->salvaLog($operation_code, $ref, $this->getAttributesToBeLoggedOnDelete(), $relation, $ref2);


        }

        return $response;

    }

    public function fetchFrom($key, $value)
    {
        $table_name = $this->getTableName();


        $db = $this->getDb();

        $value = $db->escape_string($value);

        $ris = $db->query(
            "SELECT *
            FROM $table_name
            WHERE $key = '$value'");

        return $ris->fetchAll();
    }

    /**
     * Fetches data from the database by matching JSON parameters in a given JSON column.
     *
     * @param string $json_column The name of the JSON-type column containing data to query.
     * @param array $parameters An associative array where the key is the name of the parameter and the value its expected value.
     * @return array
     * @throws \Exception
     */
    public function fetchByJsonParam(string $json_column, array $parameters): array
    {
        $table_name = $this->getTableName();

        if ($this->_columns[$json_column]->type != Column::TYPE_JSON) {
            throw new \Exception('The given column is not of type JSON.');
        }

        $query = "SELECT *
        FROM $table_name ";

        $first_condition = true;

        foreach ($parameters as $name => $value) {

            if ($first_condition) {
                $query .= " WHERE JSON_EXTRACT($json_column, '$.$name') = '$value' ";
                $first_condition = false;
                continue;
            }

            $query .= " AND JSON_EXTRACT($json_column, '$.$name') = '$value' ";
        }


        $db = $this->getDb();

        $result = $db->query($query);

        return ($result)->fetchAll();
    }

    /*
    public function useQueryBuilder(): QueryBuilder
    {
        return (new QueryBuilder($this->getTableName()));
    }*/




    public function setLogOn(Gerp_Auth $auth)
    {
        $this->auth = $auth;
        $this->log = true;
    }

    public function getTableName()
    {
        $c = get_called_class();

        $table_name = $c::TABLE_NAME;

        return $table_name;


    }

    private function isPrimaryKey($name)
    {
        $primary = $this->getPrimaryKeys();

        if (in_array($name, array_keys($primary))) {
            return true;
        }
        return false;


    }

    private function isRealUpdate()
    {
        foreach ($this->original as $key => $value) {
            if (
                property_exists($this, $key)
                && $value != $this->$key
            ) {
                return true;
            }
        }

        return false;
    }

    private function getAttributesToBeLoggedOnInsert()
    {
        $attributes = [];


        foreach (array_keys($this->_columns) as $column) {
            $attributes[$column] = $this->$column;
        }

        return json_encode(['attributes' => $attributes]);
    }


    public function getAttributesToBeLoggedOnUpdate()
    {
        $old = [];
        $attributes = [];

        foreach ( $this->original as $key => $value ) {

            $value = $value ?? '';

            if ( property_exists($this, $key) && ($value == 'null' && $this->$key=='') ) {
                continue;
            }

            if ( property_exists($this, $key) && ($this->$key == 'null' && $value=='') ) {
                continue;
            }

            if ( property_exists($this, $key) && ($value != $this->$key) ) {
                $old[$key] = $value;
                $attributes[$key] = $this->$key;
            }

        }

        if (count($old) == 0 && count($attributes) == 0) {
            return null;

        }

        return json_encode([
            'old' => $old,
            'attributes' => $attributes
        ]);

    }


    private function getAttributesToBeLoggedOnDelete()
    {
        $old = [];

        foreach (array_keys($this->_columns) as $column) {
            $old[$column] = $this->$column;
        }

        return json_encode(['old' => $old]);
    }

    /**
     * Checks the new values if the primary key is filled or not
     *
     * @param bool $autoincrement - should auto incremental value be considered as filled?
     * @return bool
     *
     * when inserting: $autoincrement is suggested to be true
     * when updating: $autoincrement is not needed
     *
     */
    private function hasPrimaryKeyValue($autoincrement = false): bool
    {
        $primary_keys = $this->getPrimaryKeys();

        foreach (array_keys($primary_keys) as $primary_key) {
            if (($this->$primary_key == null or $this->$primary_key == '')) {
                if ($autoincrement && !$this->isPrimaryKeyAutoincrement($primary_key)) {

                    return false;
                }
            }
        }

        return true;
    }

    public function upsert()
    {

        $dup = clone $this;

        if ( ! $dup->loadById()) {

            return $this->insert();
        }

        return  $this->update();

    }




}
