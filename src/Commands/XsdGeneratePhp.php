<?php

namespace NFePHP\Console\Commands;

use Goetas\XML\XSDReader\SchemaReader;
use NFePHP\Console\InputArgs\XsdGeneratePhp as XsdGeneratePhpArgs;
use NFePHP\Console\Processors\XsdGeneratePhp as XsdGeneratePhpProcessor;
use NFePHP\Console\XsdConverter\Naming\Factory as NamingFactory;
use NFePHP\Console\XsdConverter\Naming\Factory;
use NFePHP\Console\XsdConverter\PhpConverter;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class XsdGeneratePhp extends Command
{
    protected function configure()
    {
        $this->setName('xsd:generate:php');
        $this->setDescription('Generate PHP Classes from XSD files');
        $this->setDefinition(XsdGeneratePhpArgs::getDefinitions());
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $inputArgs = new XsdGeneratePhpArgs($input);

        $namingFactory = new Factory();
        $namingStrategy = $namingFactory->getNamingStrategy($inputArgs->getNamingStrategy());

        $converter = new PhpConverter($namingStrategy);

        $processor = $this->createPhpProcessor($inputArgs, $converter, new SchemaReader(), $output);
        $processor->execute($this);
        return 0;
    }

    /**
     * @param XsdGeneratePhpArgs $input
     * @param PhpConverter $converter
     * @param SchemaReader $schemaReader
     * @param OutputInterface $output
     * @return XsdGeneratePhpProcessor
     */
    public function createPhpProcessor(
        XsdGeneratePhpArgs $input,
        PhpConverter $converter,
        SchemaReader $schemaReader,
        OutputInterface $output
    ) {
    
        return new XsdGeneratePhpProcessor($input, $converter, $schemaReader, $output);
    }
}
