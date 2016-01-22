<?php

namespace NFePHP\XsdConverter;

use Goetas\Xsd\XsdToPhp\Php\PhpConverter as BasePhpConverter;

class PhpConverter extends BasePhpConverter
{

    /**
     * @param string $targetNamespace
     * @return bool
     */
    public function isNamespaceMapped($targetNamespace)
    {
        return isset($this->namespaces[$targetNamespace]) || in_array($targetNamespace, $this->namespaces);
    }
}
