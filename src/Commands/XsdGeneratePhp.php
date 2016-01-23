<?php

namespace NFePHP\Console\Commands;

use Goetas\XML\XSDReader\SchemaReader;
use NFePHP\Console\InputArgs\XsdGeneratePhp as XsdGeneratePhpArgs;
use NFePHP\Console\Processors\XsdGeneratePhp as XsdGeneratePhpProcessor;
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

        $converter = new PhpConverter($inputArgs->getNamingStrategy());

        $processor = $this->createPhpProcessor($inputArgs, $output, $converter, new SchemaReader());
        $processor->execute($this);
        return 0;
    }

    /**
     * @param XsdGeneratePhpArgs $input
     * @param OutputInterface $output
     * @param PhpConverter $converter
     * @param SchemaReader $schemaReader
     * @return XsdGeneratePhpProcessor
     */
    public function createPhpProcessor(
        XsdGeneratePhpArgs $input,
        OutputInterface $output,
        PhpConverter $converter,
        SchemaReader $schemaReader
    ) {
        return new XsdGeneratePhpProcessor($input, $output, $converter, $schemaReader);
    }
}
