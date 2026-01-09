<?php

namespace Opengerp\Tests\Database;

use Opengerp\Database\DbObject;
use PHPUnit\Framework\TestCase;

final class DbObjectTest extends TestCase
{


    public function testInsertSql()
    {
        $obj = new class extends DbObject {

            public const TABLE_NAME = 'test';
            public const TABLE_PRIMARY_KEY =   'test_id';

            public int $test_id;
            public int $test_status = 0;

            public ?int $test_nullable = null;

        };


        $obj->test_id = 1;
        $query = $obj->buildInsertQuery();


        $this->assertEquals("INSERT INTO test (test_id, test_status, test_nullable) VALUES ('1', '0', NULL) ", $query);



    }
}