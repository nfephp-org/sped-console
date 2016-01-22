<?php

namespace NFePHP\Commands\InputArgs;

use Goetas\Xsd\XsdToPhp\Naming\LongNamingStrategy;
use Goetas\Xsd\XsdToPhp\Naming\NamingStrategy;
use Goetas\Xsd\XsdToPhp\Naming\ShortNamingStrategy;
use NFePHP\DataTransferObject;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;

class XsdGeneratePhp
{
    const ARGUMENT_SOURCE = 'src';
    const OPTION_DESTINATION = 'dest';
    const OPTION_DESTIONATION_SHORT = 'd';
    const OPTION_NAMING_STRATEGY = 'naming-strategy';
    const OPTION_NAMESPACE = 'namespace';
    const OPTION_NAMESPACE_SHORT = 'ns';
    const OPTION_EXTENDS = 'extends';
    const OPTION_EXTENDS_SHORT = 'e';

    /**
     * @var InputInterface $input
     */
    private $input;

    /**
     * XsdGeneratePhp constructor.
     * @param InputInterface $input
     */
    public function __construct(InputInterface $input)
    {
        $this->input = $input;
    }

    public static function getDefinitions()
    {
        return array(
            new InputArgument(
                self::ARGUMENT_SOURCE,
                InputArgument::REQUIRED | InputArgument::IS_ARRAY,
                'Where is located your XSD definitions'
            ),
            new InputOption(
                self::OPTION_NAMESPACE,
                self::OPTION_NAMESPACE_SHORT,
                InputOption::VALUE_OPTIONAL,
                'What the namespace for generated files? Syntax: <info>NFe/</info>',
                'NFe/'
            ),
            new InputOption(
                self::OPTION_DESTINATION,
                self::OPTION_DESTIONATION_SHORT,
                InputOption::VALUE_OPTIONAL,
                'Where place the generated files? Syntax: <info>destination-directory</info>',
                './src'
            ),
            new InputOption(
                self::OPTION_NAMING_STRATEGY,
                null,
                InputOption::VALUE_REQUIRED,
                'The naming strategy for classes. short|long or class name',
                'short'
            ),
            new InputOption(
                self::OPTION_EXTENDS,
                self::OPTION_EXTENDS_SHORT,
                InputOption::VALUE_OPTIONAL,
                'all classes will extend this super class'
            ),
        );
    }

    /**
     * @return array
     */
    public function getSourceList()
    {
        return $this->input->getArgument(self::ARGUMENT_SOURCE);
    }

    /**
     * @return NamingStrategy
     */
    public function getNamingStrategy()
    {
        $namingStrategy = $this->input->getOption(self::OPTION_NAMING_STRATEGY);
        if ($namingStrategy == 'short') {
            return new ShortNamingStrategy();
        }
        if ($namingStrategy == 'long') {
            return new LongNamingStrategy();
        }
        return new $namingStrategy;
    }

    /**
     * @return string
     */
    public function getNamespace()
    {
        return $this->input->getOption(self::OPTION_NAMESPACE);
    }

    /**
     * @return string
     */
    public function getDestination()
    {
        return $this->input->getOption('dest');
    }

    public function hasExtendedClass()
    {
        return !is_null($this->input->getOption('extends'));
    }

    public function getExtendedClassName()
    {
        $ref = new \ReflectionClass($this->input->getOption('extends'));
        return $ref->getShortName();
    }

    public function getExtendedClassNamespaceName()
    {
        $ref = new \ReflectionClass($this->input->getOption('extends'));
        return $ref->getNamespaceName();
    }
}
