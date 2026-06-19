<?php

namespace DbStressmit\DependencyInjection;

// Trik kompatibilitas analisis statis (Membungkam Intelephense)
if (!class_exists('Symfony\Component\DependencyInjection\Extension\Extension')) {
    class_alias(\stdClass::class, 'Symfony\Component\DependencyInjection\Extension\Extension');
}

/**
 * Pendaftaran service otomatis ke dalam Container Dependency Injection Symfony
 */
class DbStressmitExtension extends \Symfony\Component\DependencyInjection\Extension\Extension
{
    /**
     * @param array $configs
     * @param mixed $container
     */
    public function load(array $configs, $container): void
    {
        if (!class_exists('Symfony\Component\DependencyInjection\Definition')) {
            return;
        }

        // Gunakan fully qualified class string dinamis
        $defClass = '\Symfony\Component\DependencyInjection\Definition';
        $definition = new $defClass(\DbStressmit\Stressmit::class);
        
        if (method_exists($container, 'setDefinition')) {
            $container->setDefinition('db_stressmit.analyzer', $definition);
        }
    }
}