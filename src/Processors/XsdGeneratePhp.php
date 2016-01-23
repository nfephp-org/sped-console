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
     * @param PhpConverter $converter
     * @param SchemaReader $schemaReader
     * @param OutputInterface $output [optional]
     */
    public function __construct(
        XsdGeneratePhpArgs $input,
        PhpConverter $converter,
        SchemaReader $schemaReader,
        OutputInterface $output = null
    ) {
        $this->input = $input;
        $this->output = $output;
        $this->converter = $converter;
        $this->schemaReader = $schemaReader;
        $this->loadSignatureNamespace($converter, $input);
    }

    protected function outputWriteLine($message)
    {
        if ($this->output) {
            $this->output->writeln($message);
        }
    }

    protected function outputFormatterEscape($message)
    {
        if ($this->output) {
            return $this->output->getFormatter()->escape($message);
        }
        return $message;
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
        $schemas = $this->readSchema($this->input, $this->converter);

        $targets = $this->getTargetDirectories($this->input);

        $this->printMappedNamespaces($this->converter);

        $this->convert($this->converter, $schemas, $targets, $this->input, $command);
    }

    /**
     * Print all mapped namespaces from converter.
     * @param PhpConverter $converter
     */
    protected function printMappedNamespaces(PhpConverter $converter)
    {
        $this->outputWriteLine("Namespaces:");
        foreach ($converter->getNamespaces() as $xsdTargetNamespace => $phpNamespace) {
            $this->outputWriteLine(
                " + <comment>{$this->outputFormatterEscape($xsdTargetNamespace)}</comment>" .
                " => <info>{$this->outputFormatterEscape($phpNamespace)}</info>"
            );
        }
    }

    /**
     * @param XsdGeneratePhpArgs $input
     * @return array
     */
    protected function getTargetDirectories(XsdGeneratePhpArgs $input)
    {
        $this->outputWriteLine("Destination:");
        $targets = array(
            $input->getNamespace() => $input->getDestination(),
        );
        foreach ($targets as $targetNamespace => $targetDestination) {
            if (!is_dir($targetDestination)) {
                mkdir($targetDestination, 0777, true);
            }
            $this->outputWriteLine(
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
     * @param XsdGeneratePhpCommand $command
     * @throws \Goetas\Xsd\XsdToPhp\PathGenerator\PathGeneratorException
     */
    protected function convert(
        PhpConverter $converter,
        array $schemas,
        array $targets,
        XsdGeneratePhpArgs $input,
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

        $this->outputWriteLine("Generating PHP files");

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
                $this->outputWriteLine(" + <info>" . $this->outputFormatterEscape($skippedFile) . "</info>... ");
            }
        }
    }

    /**
     * @param XsdGeneratePhpArgs $input
     * @param PhpConverter $converter
     * @return \Goetas\XML\XSDReader\Schema\Schema[]
     */
    protected function readSchema(XsdGeneratePhpArgs $input, PhpConverter $converter)
    {
        $this->outputWriteLine("Reading schemas:");
        $schemas = array();
        foreach ($input->getSourceList() as $source) {
            $schema = $this->readSource($input, $converter, $source);
            $schemas[spl_object_hash($schema)] = $schema;
        }
        return $schemas;
    }

    /**
     * @param XsdGeneratePhpArgs $input
     * @param PhpConverter $converter
     * @param string $source
     * @return \Goetas\XML\XSDReader\Schema\Schema
     */
    private function readSource(
        XsdGeneratePhpArgs $input,
        PhpConverter $converter,
        $source
    ) {
        $this->outputWriteLine(" + <comment>{$this->outputFormatterEscape($source)}</comment>");

        $xml = new \DOMDocument('1.0', 'UTF-8');
        if (!$xml->load($source)) {
            throw new \RuntimeException("Can't load the schema '{$source}'");
        }

        $targetNamespace = $xml->documentElement->getAttribute("targetNamespace");
        if (!$converter->isNamespaceMapped($targetNamespace)) {
            $converter->addNamespace($targetNamespace, $this->getPhpNamespace($input));
        }
        return $this->schemaReader->readFile($source);
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
