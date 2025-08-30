<?php

namespace Opengerp\Tests\Translation;

use PHPUnit\Framework\TestCase;

final class getYmlTest extends TestCase
{
    public function testGetYml(): void
    {
        $vett = ['test' => 'test'];

        $str = \Opengerp\Utils\Translation\Tools::getYmlFromArray($vett);

        $this->assertSame("\n" . '"test" : ', $str);
    }

    public function testFetchTermsFromCode()
    {
        $str = '<?php trans("some"); ';


        $vett_functions = [];
        $vett_functions[] = ['trans', 0];

        $results = \Opengerp\Utils\Translation\Tools::fetchTermsFromCode($str, $vett_functions);

        $this->assertEquals('some', $results[0]);
    }

    public function testFetchTermsFromTemplate()
    {
        $str = '"{"some"|trans}" ';

        $results = \Opengerp\Utils\Translation\Tools::fetchTermsFromTemplateCode($str);

        $this->assertEquals('some', $results[0]);
    }


}