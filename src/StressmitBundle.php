<?php

namespace DbStressmit;

// Membungkam komplain Undefined Type Bundle
if (!class_exists('Symfony\Component\HttpKernel\Bundle\Bundle')) {
    class_alias(\stdClass::class, 'Symfony\Component\HttpKernel\Bundle\Bundle');
}

/**
 * Symfony Bundle Wrapper Agnostik untuk db-stressmit
 */
class StressmitBundle extends \Symfony\Component\HttpKernel\Bundle\Bundle
{
    /**
     * @return mixed
     */
    public function getContainerExtension(): mixed
    {
        $extensionClass = '\DbStressmit\DependencyInjection\DbStressmitExtension';
        return new $extensionClass();
    }
}