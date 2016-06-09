<?php

namespace NFePHPTest\Console\InputArgs;

use Goetas\Xsd\XsdToPhp\Naming\LongNamingStrategy;
use Goetas\Xsd\XsdToPhp\Naming\ShortNamingStrategy;
use NFePHP\Console\InputArgs\XsdGeneratePhp;
use NFePHP\Console\XsdConverter\Naming\Factory;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;

class XsdGeneratePhpTest extends \PHPUnit_Framework_TestCase
{
    public function testShouldConstruct()
    {
        $inputMock = $this->createMock(InputInterface::class);
        $this->assertInstanceOf(XsdGeneratePhp::class, new XsdGeneratePhp($inputMock));
    }

    /**
     * @param string $method
     * @param string $optionName
     * @param string $value
     * @param string $expectedValue
     * @dataProvider provideSuccessfulInputArgument
     */
    public function testTestInputArgumentWrapper($method, $optionName, $value, $expectedValue = null)
    {
        $inputMock = $this->createMock(InputInterface::class);
        $inputMock->expects($this->any())->method('getArgument')->with($this->equalTo($optionName))->willReturn($value);
        $inputArgs = new XsdGeneratePhp($inputMock);
        $this->assertEquals($expectedValue, call_user_func(array($inputArgs, $method)));
    }

    public function provideSuccessfulInputArgument()
    {
        return array(
            'source' => array('getSourceList', XsdGeneratePhp::ARGUMENT_SOURCE, array('./src'), array('./src')),
        );
    }

    /**
     * @param string $method
     * @param string $optionName
     * @param string $value
     * @param string $expectedValue
     * @dataProvider provideSuccessfulInputOption
     */
    public function testTestInputOptionWrapper($method, $optionName, $value, $expectedValue = null)
    {
        $inputMock = $this->createMock(InputInterface::class);
        $inputMock->expects($this->any())->method('getOption')->with($this->equalTo($optionName))->willReturn($value);
        $inputArgs = new XsdGeneratePhp($inputMock);
        $this->assertEquals($expectedValue, call_user_func(array($inputArgs, $method)));
    }

    public function provideSuccessfulInputOption()
    {
        return array(
            'destination' => array('getDestination', XsdGeneratePhp::OPTION_DESTINATION, './src', './src'),
            'namespace' => array('getNamespace', XsdGeneratePhp::OPTION_NAMESPACE, 'NFe', 'NFe'),
            'parent class name' => array('getExtendedClassName', XsdGeneratePhp::OPTION_EXTENDS, \NFePHP\Console\Commands\XsdGeneratePhp::class, 'XsdGeneratePhp'),
            'parent class namespace' => array('getExtendedClassNamespaceName', XsdGeneratePhp::OPTION_EXTENDS, \NFePHP\Console\Commands\XsdGeneratePhp::class, 'NFePHP\Console\Commands'),
            'has parent class' => array('hasExtendedClass', XsdGeneratePhp::OPTION_EXTENDS, \NFePHP\Console\Commands\XsdGeneratePhp::class, true),
            'short naming strategy' => array('getNamingStrategy', XsdGeneratePhp::OPTION_NAMING_STRATEGY, 'short', Factory::NAMING_SHORT),
            'long naming strategy' => array('getNamingStrategy', XsdGeneratePhp::OPTION_NAMING_STRATEGY, 'long', Factory::NAMING_LONG),
            'sped naming strategy' => array('getNamingStrategy', XsdGeneratePhp::OPTION_NAMING_STRATEGY, 'sped', Factory::NAMING_SPED),
        );
    }

    public function testAssertDefinitions()
    {
        $definitions = XsdGeneratePhp::getDefinitions();
        $expectedDefinitions = array(
            array(
                'name' => XsdGeneratePhp::ARGUMENT_SOURCE,
                'is_array' => true,
                'is_required' => true,
                'mode' => InputArgument::REQUIRED | InputArgument::IS_ARRAY,
                'description' => 'Where is located your XSD definitions',
                'default' => array(),
            ),
            array(
                'name' => XsdGeneratePhp::OPTION_NAMESPACE,
                'shortcut' => XsdGeneratePhp::OPTION_NAMESPACE_SHORT,
                'is_optional' => true,
                'is_required' => false,
                'description' => 'What the namespace for generated files? Syntax: <info>NFe/</info>',
                'default' => 'NFe/'
            ),
            array(
                'name' => XsdGeneratePhp::OPTION_DESTINATION,
                'shortcut' => XsdGeneratePhp::OPTION_DESTIONATION_SHORT,
                'is_optional' => true,
                'is_required' => false,
                'description' => 'Where place the generated files? Syntax: <info>destination-directory</info>',
                'default' => './src'
            ),
            array(
                'name' => XsdGeneratePhp::OPTION_NAMING_STRATEGY,
                'shortcut' => null,
                'is_optional' => false,
                'is_required' => true,
                'description' => 'The naming strategy for classes. (sped, short, long)',
                'default' => 'sped',
            ),
            array(
                'name' => XsdGeneratePhp::OPTION_EXTENDS,
                'shortcut' => XsdGeneratePhp::OPTION_EXTENDS_SHORT,
                'is_optional' => true,
                'is_required' => false,
                'description' => 'all classes will extend this super class',
                'default' => null,
            ),
        );

        foreach ($expectedDefinitions as $key => $defitinion) {
            $this->assertEquals($defitinion['name'], $definitions[$key]->getName());
            $this->assertEquals($defitinion['description'], $definitions[$key]->getDescription());
            $this->assertEquals($defitinion['default'], $definitions[$key]->getDefault());
            if ($definitions[$key] instanceof InputArgument) {
                $this->assertEquals($defitinion['is_required'], $definitions[$key]->isRequired());
                $this->assertEquals($defitinion['is_array'], $definitions[$key]->isArray());
            }
            if ($definitions[$key] instanceof InputOption) {
                $this->assertEquals($defitinion['is_required'], $definitions[$key]->isValueRequired());
                $this->assertEquals($defitinion['is_optional'], $definitions[$key]->isValueOptional());
                $this->assertEquals($defitinion['shortcut'], $definitions[$key]->getShortcut());
            }
        }
    }
}
