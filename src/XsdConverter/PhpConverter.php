<?php

namespace NFePHP\Console\XsdConverter;

use Goetas\Xsd\XsdToPhp\Php\PhpConverter as BasePhpConverter;

class PhpConverter extends BasePhpConverter
{
    /**
     * @return array
     */
    public function getNamespaces()
    {
        return $this->namespaces;
    }

    /**
     * @param string $targetNamespace
     * @return bool
     */
    public function isNamespaceMapped($targetNamespace)
    {
        return isset($this->namespaces[$targetNamespace]) || in_array($targetNamespace, $this->namespaces);
    }
}
