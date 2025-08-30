<?php

namespace Opengerp\Tests\Files;

use PHPUnit\Framework\TestCase;

final class SearchTest extends TestCase
{
    public function testSearch(): void
    {

        $dir = 'src/Utils/Files/';
        $vett = \Opengerp\Utils\Files\Search::readDirRecursiveAsArray($dir, 'php');

        $this->assertEquals('src/Utils/Files/Search.php', $vett[0]['nome']);
    }
}