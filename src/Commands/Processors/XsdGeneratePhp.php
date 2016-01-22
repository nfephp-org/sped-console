<?php

namespace NFePHP\Commands\Processors;

use Goetas\XML\XSDReader\SchemaReader;
use Goetas\Xsd\XsdToPhp\Php\ClassGenerator;
use Goetas\Xsd\XsdToPhp\Php\PathGenerator\Psr4PathGenerator;
use Goetas\Xsd\XsdToPhp\Php\Structure\PHPClass;
use NFePHP\Commands\InputArgs\XsdGeneratePhp as XsdGeneratePhpArgs;
use NFePHP\Commands\XsdGeneratePhp as XsdGeneratePhpCommand;
use NFePHP\XsdConverter\PhpConverter;
use Symfony\Component\Console\Helper\ProgressHelper;
use Symfony\Component\Console\Output\OutputInterface;
use Zend\Code\Generator\FileGenerator;

class XsdGeneratePhp
{
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
     * @var array
     */
    private $targetNamespaces;

    /**
     * XsdGeneratePhp constructor.
     * @param XsdGeneratePhpArgs $input
     * @param OutputInterface $output
     * @param PhpConverter $converter
     * @param SchemaReader $schemaReader
     * @param array $targetNamespaces
     */
    public function __construct(
        XsdGeneratePhpArgs $input,
        OutputInterface $output,
        PhpConverter $converter,
        SchemaReader $schemaReader,
        array $targetNamespaces
    )
    {
        $this->input = $input;
        $this->output = $output;
        $this->converter = $converter;
        $this->schemaReader = $schemaReader;
        $this->targetNamespaces = $targetNamespaces;
    }

    /**
     * @param string $targetNamespace
     */
    public function addTargetNamespace($targetNamespace)
    {
        $this->targetNamespaces[] = $targetNamespace;
    }

    public function execute(XsdGeneratePhpCommand $command)
    {
        $this->mapXsdTargetNamespaces($this->input, $this->output, $this->converter);

        $targets = $this->getTargetDirectories($this->input, $this->output);

        $schemas = $this->readSchema($this->input, $this->output, $this->converter);

        $this->convert($this->converter, $schemas, $targets, $this->input, $this->output, $command);
    }

    private function mapXsdTargetNamespaces(XsdGeneratePhpArgs $input, OutputInterface $output, PhpConverter $converter)
    {
        $output->writeln("Namespaces:");
        foreach ($this->targetNamespaces as $xsdTargetNamespace) {
            $converter->addNamespace($xsdTargetNamespace, trim(strtr($input->getNamespace(), "./", "\\\\"), "\\"));
            $output->writeln(" + <comment>$xsdTargetNamespace</comment> => <info>{$input->getNamespace()} </info>");
        }
    }

    private function getTargetDirectories(XsdGeneratePhpArgs $input, OutputInterface $output)
    {
        $output->writeln("Target directories:");
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

    protected function convert(
        PhpConverter $converter,
        array $schemas,
        array $targets,
        XsdGeneratePhpArgs $input,
        OutputInterface $output,
        XsdGeneratePhpCommand $command
    )
    {
        $generator = new ClassGenerator();
        $pathGenerator = new Psr4PathGenerator($targets);

        /** @var ProgressHelper $progressBar */
        $progressBar = $command->getHelper('progress');
        $items = $converter->convert($schemas);
        $progressBar->start($this->output, count($items));

        $extendClass = null;
        if($input->hasExtendedClass()){
            $extendClass = new PHPClass($input->getExtendedClassName(), $input->getExtendedClassNamespaceName());
        }
        /** @var PHPClass $item */
        foreach ($items as $item) {
            $progressBar->advance(1, true);
            $output->write(" + <info>" . $output->getFormatter()->escape($item->getFullName()) . "</info>... ");
            $path = $pathGenerator->getPath($item);


            $fileGen = new FileGenerator();
            $fileGen->setFilename($path);
            $classGen = new \Zend\Code\Generator\ClassGenerator();

            if (!$item->getExtends() instanceof PHPClass && $extendClass instanceof PHPClass) {
                $item->setExtends($extendClass);
            }

            $message = 'skip.';
            if ($generator->generate($classGen, $item)) {

                $fileGen->setClass($classGen);
                $fileGen->write();
                $message = 'done.';
            }
            $output->writeln($message);
        }
        $progressBar->finish();
    }

    private function readSchema(XsdGeneratePhpArgs $input, OutputInterface $output, PhpConverter $converter)
    {
        $reader = new SchemaReader();
        $schemas = array();
        foreach ($input->getSourceList() as $source) {
            try {
                $schema = $this->readSource($reader, $source, $converter, $output);
                $schemas[spl_object_hash($schema)] = $schema;
            } catch (\DomainException $exception) {
                $message = preg_replace(
                    '/^The namespace (.*) is not /',
                    ' - Skipped The namespace <comment>$1</comment> is not ',
                    $exception->getMessage()
                );
                $output->writeln($message);
            }
        }
        return $schemas;
    }

    private function readSource(SchemaReader $reader, $source, PhpConverter $converter, OutputInterface $output)
    {
        $output->writeln("Reading <comment>$source</comment>");

        $xml = new \DOMDocument('1.0', 'UTF-8');
        if (!$xml->load($source)) {
            throw new \RuntimeException("Can't load the schema '{$source}'");
        }

        $targetNamespace = $xml->documentElement->getAttribute("targetNamespace");
        if (!$converter->isNamespaceMapped($targetNamespace)) {
            throw new \DomainException('The namespace ' . $targetNamespace . ' is not mapped with PHP namespace');
        }
        return $reader->readFile($source);
    }
}
