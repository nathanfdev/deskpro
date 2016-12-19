<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2016, DeskPRO Ltd.
 *
 * The license agreement under which this software is released
 * can be found at https://www.deskpro.com/eula/
 *
 * By using this software, you acknowledge having read the license
 * and agree to be bound thereby.
 *
 * Please note that DeskPRO is not free software. We release the full
 * source code for our software because we trust our users to pay us for
 * the huge investment in time and energy that has gone into both creating
 * this software and supporting our customers. By providing the source code
 * we preserve our customers' ability to modify, audit and learn from our
 * work. We have been developing DeskPRO since 2001, please help us make it
 * another decade.
 *
 * Like the work you see? Think you could make it better? We are always
 * looking for great developers to join us: http://www.deskpro.com/jobs/
 *
 * ~ Thanks, Everyone at Team DeskPRO
 */

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
