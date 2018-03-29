<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\AppBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * This replaces doctrine FileCache instances with our custom implementation
 * which uses a different filename algo.
 *
 * Doctrine's by default tries to encode the file name to be a bin2hex of the $id.
 * This creates very long filenames which breaks on Windows. Doctrine has special
 * handling for Windows, but this doesnt matter for us because we build/pre-cache
 * on Linux anyway, so the .zip ends up having these long filenames that break Windows.
 *
 * So the easiest solution is to just override the few cases (e.g. annotations) cache
 * adapters to use our own.
 */
class DoctrineCacheClassPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        foreach ($container->getDefinitions() as $service_id => $def) {
            $c = $def->getClass();

            if ($c === 'Doctrine\Common\Cache\FilesystemCache') {
                $def->setClass('DeskPRO\Component\Doctrine\Common\Cache\FilesystemCache');
            } elseif ($c === 'Doctrine\Common\Cache\PhpFileCache') {
                $def->setClass('DeskPRO\Component\Doctrine\Common\Cache\PhpFileCache');
            }
        }
    }
}
