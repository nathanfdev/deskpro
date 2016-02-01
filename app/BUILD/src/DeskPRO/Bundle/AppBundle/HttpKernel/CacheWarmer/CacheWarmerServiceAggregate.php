<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2015, DeskPRO Ltd.
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

namespace DeskPRO\Bundle\AppBundle\HttpKernel\CacheWarmer;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\CacheWarmer\CacheWarmerInterface;

/**
 * Similar to the default.
 */
class CacheWarmerServiceAggregate implements CacheWarmerInterface
{
    private $container;

    /**
     * @var string[]
     */
    private $warmerServiceIds;

    /**
     * @var bool
     */
    private $optionalsEnabled = false;

    public function __construct(ContainerInterface $container, array $warmerServiceIds)
    {
        $this->container        = $container;
        $this->warmerServiceIds = $warmerServiceIds;

        foreach ($warmerServiceIds as $sid) {
            if (!$container->has($sid)) {
                throw new \InvalidArgumentException("No such service: $sid");
            }
        }
    }

    public function enableOptionalWarmers()
    {
        $this->optionalsEnabled = true;
    }

    /**
     * {@inheritdoc}
     */
    public function isOptional()
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function warmUp($cacheDir)
    {
        foreach ($this->warmerServiceIds as $sid) {
            $warmer = $this->container->get($sid);
            if (!$warmer instanceof CacheWarmerInterface) {
                throw new \RuntimeException("Invalid warmer service does not implement CacheWarmerInterface: $sid");
            }
            if (!$this->optionalsEnabled && $warmer->isOptional()) {
                continue;
            }
            $warmer->warmUp($cacheDir);
        }
    }
}
