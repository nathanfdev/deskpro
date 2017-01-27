<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2017, DeskPRO Ltd.
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

namespace DeskPRO\Bundle\UpdateBundle\Service;

use DeskPRO\Bundle\UpdateBundle\Instance\InstanceReader;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\Filesystem\Filesystem;

class UpdateCleanup
{
    /**
     * @var string
     */
    protected $current;

    /**
     * @var InstanceReader
     */
    protected $reader;

    public function __construct(Container $container)
    {
        $this->current = $container->get('deskpro.app_env')->getAppName();
        $this->reader  = $container->get('dp.updater.instance_reader');
    }

    public function cleanup()
    {
        if (defined('DPC_IS_CLOUD')) {
            return;
        }

        if (!is_numeric($this->current)) {
            return;
        }

        $fs    = new Filesystem();
        $paths = [
            $this->reader->getAppBasePath(),
            $this->reader->getAssetsBasePath(),
            $this->reader->getKernelCacheBasePath(),
        ];

        foreach ($paths as $path) {
            $pattern = $path.DIRECTORY_SEPARATOR.'[0-9]*';

            foreach (glob($pattern) as $entry) {
                $name = pathinfo($entry, PATHINFO_FILENAME);

                // skip non-numeric dirs ("BUILD", "run", etc)
                if (!is_numeric($name)) {
                    continue;
                }

                $current = (int) $this->current;
                $name    = (int) $name;

                if ($current - 1 <= $name) {
                    continue;
                }

                $fs->remove($entry);
            }
        }
    }
}
