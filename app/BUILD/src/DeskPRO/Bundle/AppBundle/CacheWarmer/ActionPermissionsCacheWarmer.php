<?php

namespace DeskPRO\Bundle\AppBundle\CacheWarmer;

use DeskPRO\Bundle\AppBundle\ApiTag\TagsCollector;
use Symfony\Component\HttpKernel\CacheWarmer\CacheWarmerInterface;

/**
 * Class ActionPermissionsCacheWarmer.
 */
class ActionPermissionsCacheWarmer implements CacheWarmerInterface
{
    /**
     * @var \DeskPRO\Bundle\AppBundle\ApiTag\TagsCollector
     */
    protected $tagsCollector;

    /**
     * Constructor.
     *
     * @param TagsCollector $tagsCollector
     */
    public function __construct(TagsCollector $tagsCollector)
    {
        $this->tagsCollector = $tagsCollector;
    }

    /**
     * {@inheritdoc}
     */
    public function warmUp($cacheDir)
    {
        $this->tagsCollector->collectTags(true);
    }

    /**
     * {@inheritdoc}
     */
    public function isOptional()
    {
        return true;
    }
}
