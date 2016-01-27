<?php

namespace NFePHP\Console\XsdConverter\Naming;

use Goetas\Xsd\XsdToPhp\Naming\LongNamingStrategy;
use Goetas\Xsd\XsdToPhp\Naming\NamingStrategy;
use Goetas\Xsd\XsdToPhp\Naming\ShortNamingStrategy;

class Factory
{
    const NAMING_SPED = 'sped';
    const NAMING_SHORT = 'short';
    const NAMING_LONG = 'long';

    private static $strategies = array(
        self::NAMING_SPED => SpedStrategy::class,
        self::NAMING_SHORT => ShortNamingStrategy::class,
        self::NAMING_LONG => LongNamingStrategy::class,
    );

    /**
     * @param string $namingStrategy
     * @return string
     * @throws \InvalidArgumentException
     */
    private static function getClassName($namingStrategy)
    {
        if (!isset(self::$strategies[$namingStrategy])) {
            throw new \InvalidArgumentException(
                "The given 'naming strategy' " . $namingStrategy . " is unknown, " .
                "it currently supports only the following strategies: " .
                implode(", ", self::getAvailableNamingStrategies())
            );
        }
        return self::$strategies[$namingStrategy];
    }

    /**
     * Returns the list of supported naming strategies.
     *
     * @return array List of supported strategies.
     */
    public static function getAvailableNamingStrategies()
    {
        return array_keys(self::$strategies);
    }

    /**
     * Adds a new supported driver.
     *
     * @param string $name Driver's name
     * @param string $className Class name of driver
     * @return void
     * @throws \InvalidArgumentException
     */
    public static function addAvailableNamingStrategy($name, $className)
    {
        $ref = new \ReflectionClass($className);
        if (!$ref->isSubclassOf(NamingStrategy::class)) {
            throw new \InvalidArgumentException($className);
        }
        self::$strategies[$name] = $ref->getName();
    }

    /**
     * @param string $name
     * @return NamingStrategy
     */
    public function getNamingStrategy($name)
    {
        $namingStrategyClass = self::getClassName($name);
        return new $namingStrategyClass;
    }
}
