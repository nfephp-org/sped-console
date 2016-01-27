<?php

namespace NFePHPTest\Console\XsdConverter\Naming;


use Goetas\Xsd\XsdToPhp\Naming\NamingStrategy;
use NFePHP\Console\XsdConverter\Naming\Factory;
use NFePHP\Console\XsdConverter\Naming\SpedStrategy;

class FactoryTest extends \PHPUnit_Framework_TestCase
{

    public function testShouldCreateNamingStrategyInstance()
    {
        $factory = new Factory();
        $strategy = $factory->getNamingStrategy(Factory::NAMING_SPED);
        $this->assertInstanceOf(SpedStrategy::class, $strategy);
    }

    public function testShouldAddANewNamingStrategy()
    {
        $this->assertCount(3, Factory::getAvailableNamingStrategies());

        $strategy = $this->getMockForAbstractClass(NamingStrategy::class);

        Factory::addAvailableNamingStrategy('std', $strategy);

        $this->assertCount(4, Factory::getAvailableNamingStrategies());
        $this->assertTrue(in_array('std', Factory::getAvailableNamingStrategies()));
    }

    public function testTrytoGetUnkownNamingStrategyAndExpectAException()
    {
        $this->setExpectedException(\InvalidArgumentException::class);
        $factory = new Factory();
        $factory->getNamingStrategy('default');
    }

    public function testTryToAddNamingStrategyAndExpectAException()
    {
        $this->setExpectedException(\InvalidArgumentException::class);
        Factory::addAvailableNamingStrategy('default', \stdclass::class);
    }
}
