<?php

namespace NFePHP\Console\Processors;

use Goetas\XML\XSDReader\SchemaReader;
use Goetas\Xsd\XsdToPhp\Php\ClassGenerator;
use Goetas\Xsd\XsdToPhp\Php\PathGenerator\Psr4PathGenerator;
use Goetas\Xsd\XsdToPhp\Php\Structure\PHPClass;
use NFePHP\Console\InputArgs\XsdGeneratePhp as XsdGeneratePhpArgs;
use NFePHP\Console\Commands\XsdGeneratePhp as XsdGeneratePhpCommand;
use NFePHP\Console\XsdConverter\PhpConverter;
use Symfony\Component\Console\Helper\ProgressHelper;
use Symfony\Component\Console\Output\OutputInterface;
use Zend\Code\Generator\FileGenerator;

class XsdGeneratePhp
{
    const XSD_SIGNATURE_NAMESPACE = 'http://www.w3.org/2000/09/xmldsig#';

    /**
     * @var XsdGeneratePhpArgs
     */
    private $input;

    /**
     * @var OutputInterface
     */
    private $output;

    /**
     * @var PhpConverter
     */
    private $converter;

    /**
     * @var SchemaReader
     */
    private $schemaReader;

    /**
     * XsdGeneratePhp constructor.
     * @param XsdGeneratePhpArgs $input
     * @param OutputInterface $output
     * @param PhpConverter $converter
     * @param SchemaReader $schemaReader
     */
    public function __construct(
        XsdGeneratePhpArgs $input,
        OutputInterface $output,
        PhpConverter $converter,
        SchemaReader $schemaReader
    ) {
        $this->input = $input;
        $this->output = $output;
        $this->converter = $converter;
        $this->schemaReader = $schemaReader;
        $this->loadSignatureNamespace($converter, $input);
    }

    /**
     * @param PhpConverter $converter
     * @param XsdGeneratePhpArgs $input
     * @return void
     */
    private function loadSignatureNamespace(PhpConverter $converter, XsdGeneratePhpArgs $input)
    {
        $converter->addNamespace(self::XSD_SIGNATURE_NAMESPACE, $this->getPhpNamespace($input));
    }

    /**
     * @param XsdGeneratePhpCommand $command
     */
    public function execute(XsdGeneratePhpCommand $command)
    {
        $schemas = $this->readSchema($this->input, $this->output, $this->converter);

        $targets = $this->getTargetDirectories($this->input, $this->output);

        $this->printMappedNamespaces($this->output, $this->converter);

        $this->convert($this->converter, $schemas, $targets, $this->input, $this->output, $command);
    }

    /**
     * Print all mapped namespaces from converter.
     * @param OutputInterface $output
     * @param PhpConverter $converter
     */
    private function printMappedNamespaces(OutputInterface $output, PhpConverter $converter)
    {
        $output->writeln("Namespaces:");
        foreach ($converter->getNamespaces() as $xsdTargetNamespace => $phpNamespace) {
            $output->writeln(
                " + <comment>{$output->getFormatter()->escape($xsdTargetNamespace)}</comment>" .
                " => <info>{$output->getFormatter()->escape($phpNamespace)}</info>"
            );
        }
    }

    /**
     * @param XsdGeneratePhpArgs $input
     * @param OutputInterface $output
     * @return array
     */
    private function getTargetDirectories(XsdGeneratePhpArgs $input, OutputInterface $output)
    {
        $output->writeln("Destination:");
        $targets = array(
            $input->getNamespace() => $input->getDestination(),
        );
        foreach ($targets as $targetNamespace => $targetDestination) {
            if (!is_dir($targetDestination)) {
                mkdir($targetDestination, 0777, true);
            }
            $output->writeln(
                ' + <comment>' . strtr($targetNamespace, "\\", "/") .
                ' </comment>=> <info>' . $targetDestination . '</info>'
            );
        }
        return $targets;
    }

    /**
     * @param PhpConverter $converter
     * @param array $schemas
     * @param array $targets
     * @param XsdGeneratePhpArgs $input
     * @param OutputInterface $output
     * @param XsdGeneratePhpCommand $command
     * @throws \Goetas\Xsd\XsdToPhp\PathGenerator\PathGeneratorException
     */
    protected function convert(
        PhpConverter $converter,
        array $schemas,
        array $targets,
        XsdGeneratePhpArgs $input,
        OutputInterface $output,
        XsdGeneratePhpCommand $command
    ) {
        $generator = new ClassGenerator();
        $pathGenerator = new Psr4PathGenerator($targets);

        /** @var ProgressHelper $progressBar */
        $progressBar = $command->getHelper('progress');
        $items = $converter->convert($schemas);
        $progressBar->start($this->output, count($items));

        $extendClass = null;
        if ($input->hasExtendedClass()) {
            $extendClass = new PHPClass($input->getExtendedClassName(), $input->getExtendedClassNamespaceName());
        }

        $output->writeln("Generating PHP files");

        $skippedFiles = array();
        /** @var PHPClass $item */
        foreach ($items as $item) {
            $progressBar->advance(1, true);
            $path = $pathGenerator->getPath($item);

            $fileGen = new FileGenerator();
            $fileGen->setFilename($path);
            $classGen = new \Zend\Code\Generator\ClassGenerator();

            if (!$item->getExtends() instanceof PHPClass && $extendClass instanceof PHPClass) {
                $item->setExtends($extendClass);
            }

            if (!$generator->generate($classGen, $item)) {
                $skippedFiles[] = $item->getFullName();
            }
            $fileGen->setClass($classGen);
            $fileGen->write();
        }
        $progressBar->finish();

        if (!empty($skippedFiles)) {
            foreach ($skippedFiles as $skippedFile) {
                $output->write(" + <info>" . $output->getFormatter()->escape($skippedFile) . "</info>... ");
            }
        }
    }

    /**
     * @param XsdGeneratePhpArgs $input
     * @param OutputInterface $output
     * @param PhpConverter $converter
     * @return \Goetas\XML\XSDReader\Schema\Schema[]
     */
    private function readSchema(XsdGeneratePhpArgs $input, OutputInterface $output, PhpConverter $converter)
    {
        $reader = new SchemaReader();
        $schemas = array();
        foreach ($input->getSourceList() as $source) {
            $schema = $this->readSource($input, $output, $converter, $reader, $source);
            $schemas[spl_object_hash($schema)] = $schema;
        }
        return $schemas;
    }

    /**
     * @param XsdGeneratePhpArgs $input
     * @param OutputInterface $output
     * @param PhpConverter $converter
     * @param SchemaReader $reader
     * @param string $source
     * @return \Goetas\XML\XSDReader\Schema\Schema
     */
    private function readSource(
        XsdGeneratePhpArgs $input,
        OutputInterface $output,
        PhpConverter $converter,
        SchemaReader $reader,
        $source
    ) {
        $output->writeln("Reading:");
        $output->writeln(" + <comment>{$output->getFormatter()->escape($source)}</comment>");

        $xml = new \DOMDocument('1.0', 'UTF-8');
        if (!$xml->load($source)) {
            throw new \RuntimeException("Can't load the schema '{$source}'");
        }

        $targetNamespace = $xml->documentElement->getAttribute("targetNamespace");
        if (!$converter->isNamespaceMapped($targetNamespace)) {
            $converter->addNamespace($targetNamespace, $this->getPhpNamespace($input));
        }
        return $reader->readFile($source);
    }

    /**
     * @param XsdGeneratePhpArgs $input
     * @return string
     */
    private function getPhpNamespace(XsdGeneratePhpArgs $input)
    {
        return trim(strtr($input->getNamespace(), "./", "\\\\"), "\\");
    }
}
