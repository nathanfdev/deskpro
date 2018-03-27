<?php

namespace DeskPRO\Bundle\AppBundle\HttpKernel\CacheWarmer;

use Doctrine\ORM\EntityManager;
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

    /**
     * Constructor.
     *
     * @param ContainerInterface $container
     * @param array              $warmerServiceIds
     */
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
        // we need to ensure that proxy auto generation is disabled to warm up doctrine proxy cache
        // otherwise it will be just skipped
        /** @var EntityManager[] $ems */
        $ems = $this->container->get('doctrine')->getManagers();
        foreach ($ems as $em) {
            $em->getConfiguration()->setAutoGenerateProxyClasses(false);
        }

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
