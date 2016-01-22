<?php

namespace NFePHPTest\XsdConverter;

use Goetas\Xsd\XsdToPhp\Naming\ShortNamingStrategy;
use NFePHP\XsdConverter\PhpConverter;

class PhpConverterTest extends \PHPUnit_Framework_TestCase
{

    public function testIsMappedNamespace()
    {
        $converter = new PhpConverter(new ShortNamingStrategy());
        $converter->addNamespace('http://my.namespace.com', 'My\Namespace');
        $this->assertTrue($converter->isNamespaceMapped('http://my.namespace.com'));
        $this->assertTrue($converter->isNamespaceMapped('My\Namespace'));
    }
}
