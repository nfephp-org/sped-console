<?php

namespace NFePHPTest\Console\XsdConverter;

use Goetas\Xsd\XsdToPhp\Naming\ShortNamingStrategy;
use NFePHP\Console\XsdConverter\PhpConverter;

class PhpConverterTest extends \PHPUnit_Framework_TestCase
{

    public function testIsMappedNamespace()
    {
        $namingStrategeyMock = $this->createMock(ShortNamingStrategy::class);
        $converter = new PhpConverter($namingStrategeyMock);
        $converter->addNamespace('http://my.namespace.com', 'My\Namespace');
        $this->assertCount(3, $converter->getNamespaces());
        $this->assertTrue($converter->isNamespaceMapped('http://my.namespace.com'));
        $this->assertTrue($converter->isNamespaceMapped('My\Namespace'));
    }
}
