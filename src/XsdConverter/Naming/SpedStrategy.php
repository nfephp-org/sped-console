<?php

namespace NFePHP\Console\XsdConverter\Naming;

use Doctrine\Common\Inflector\Inflector;
use GoetasWebservices\XML\XSDReader\Schema\Item;
use GoetasWebservices\XML\XSDReader\Schema\Type\Type;
use Goetas\Xsd\XsdToPhp\Naming\NamingStrategy;

class SpedStrategy implements NamingStrategy
{
    public function getTypeName(Type $type)
    {
        return $this->classify($type->getName()) . "Type";
    }

    public function getAnonymousTypeName(Type $type, $parentName)
    {
        return $this->classify($parentName) . "Type";
    }

    public function getItemName(Item $item)
    {
        return $this->classify($item->getName());
    }

    /**
     * @param Item $item
     * @return string
     */
    public function getPropertyName($item)
    {
        return str_replace(".", " ", $item->getName());
    }

    private function classify($name)
    {
        return Inflector::classify(str_replace(".", " ", $name));
    }
}
