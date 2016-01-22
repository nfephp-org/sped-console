<?php

namespace NFePHPTest\Commands;


use NFePHP\Commands\XsdGeneratePhp;
use Symfony\Component\Console\Command\Command;

class XsdGeneratePhpTest extends \PHPUnit_Framework_TestCase
{

    public function testShouldConstruct()
    {
        $this->assertInstanceOf(Command::class, new XsdGeneratePhp());
    }
}
