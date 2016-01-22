<?php

namespace NFePHPTest\Console\Commands;


use NFePHP\Console\Commands\XsdGeneratePhp;
use Symfony\Component\Console\Command\Command;

class XsdGeneratePhpTest extends \PHPUnit_Framework_TestCase
{

    public function testShouldConstruct()
    {
        $this->assertInstanceOf(Command::class, new XsdGeneratePhp());
    }
}
