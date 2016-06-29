<?php

namespace NFePHPTest\Console\Processors;

use GoetasWebservices\XML\XSDReader\SchemaReader;
use NFePHP\Console\Commands\XsdGeneratePhp as XsdGeneratePhpCommand;
use NFePHP\Console\InputArgs\XsdGeneratePhp as XsdGeneratePhpArgs;
use NFePHP\Console\Processors\XsdGeneratePhp;
use NFePHP\Console\XsdConverter\PhpConverter;

class XsdGeneratePhpTest extends \PHPUnit_Framework_TestCase
{

    public function testShoudInstanciate()
    {
        $input = $this
            ->getMockBuilder(XsdGeneratePhpArgs::class)
            ->disableOriginalConstructor()
            ->getMock();
        $converter = $this
            ->getMockBuilder(PhpConverter::class)
            ->disableOriginalConstructor()
            ->getMock();
        $schemaReader = $this->createMock(SchemaReader::class);
        $this->assertInstanceOf(XsdGeneratePhp::class, new XsdGeneratePhp($input, $converter, $schemaReader));
    }

    public function testExecute()
    {
        $input = $this
            ->getMockBuilder(XsdGeneratePhpArgs::class)
            ->disableOriginalConstructor()
            ->getMock();
        $converter = $this
            ->getMockBuilder(PhpConverter::class)
            ->disableOriginalConstructor()
            ->getMock();
        $schemaReader = $this->createMock(SchemaReader::class);

        $command = $this
            ->getMockBuilder(XsdGeneratePhpCommand::class)
            ->disableOriginalConstructor()
            ->getMock();

        $processor = $this
            ->getMockBuilder(XsdGeneratePhp::class)
            ->setConstructorArgs(array($input, $converter, $schemaReader))
            ->setMethods(
                array(
                    'readSchema',
                    'getTargetDirectories',
                    'printMappedNamespaces',
                    'convert'
                )
            )
            ->getMock();

        $processor->expects($this->once())->method('readSchema')->willReturn(array());
        $processor->expects($this->once())->method('getTargetDirectories')->willReturn(array());
        $processor->expects($this->once())->method('printMappedNamespaces')->willReturn(null);
        $processor->expects($this->once())->method('convert')->willReturn(null);

        $processor->execute($command);
    }
}
