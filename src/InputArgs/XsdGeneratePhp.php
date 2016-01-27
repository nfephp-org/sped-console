<?php

namespace NFePHP\Console\InputArgs;

use NFePHP\Console\XsdConverter\Naming\Factory as NamingFactory;
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
                'The naming strategy for classes. (' .
                implode(', ', NamingFactory::getAvailableNamingStrategies()) .
                ')',
                NamingFactory::NAMING_SPED
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
     * @return string
     */
    public function getNamingStrategy()
    {
        return $this->input->getOption(self::OPTION_NAMING_STRATEGY);
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
