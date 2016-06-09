<?php

namespace NFePHPTest\Console\Commands;


use NFePHP\Console\Commands\XsdGeneratePhp;
use NFePHP\Console\Processors\XsdGeneratePhp as XsdGeneratePhpProcessor;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class XsdGeneratePhpTest extends \PHPUnit_Framework_TestCase
{

    public function testShouldConstruct()
    {
        $this->assertInstanceOf(Command::class, new XsdGeneratePhp());
    }

    public function testExecute()
    {
        $input = $this->getMockForAbstractClass(InputInterface::class, array('getOption'));
        $input
            ->expects($this->once())
            ->method('getOption')
            ->with($this->equalTo('naming-strategy'))
            ->willReturn('short');

        $output = $this->createMock(OutputInterface::class);
        $processor = $this
            ->getMockBuilder(XsdGeneratePhpProcessor::class)
            ->disableOriginalConstructor()
            ->getMock();

        $command = $this->getMockBuilder(XsdGeneratePhp::class)
            ->setMethods(['createPhpProcessor'])
            ->getMock();
        $command->expects($this->once())->method('createPhpProcessor')->willReturn($processor);

        $command->run($input, $output);
    }
}
