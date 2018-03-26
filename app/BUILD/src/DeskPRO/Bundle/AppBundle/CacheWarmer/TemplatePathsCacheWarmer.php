<?php

namespace DeskPRO\Bundle\AppBundle\CacheWarmer;

use Symfony\Bundle\FrameworkBundle\CacheWarmer\TemplatePathsCacheWarmer as BaseTemplatePathsCacheWarmer;

/**
 * Class TemplatePathsCacheWarmer.
 */
class TemplatePathsCacheWarmer extends BaseTemplatePathsCacheWarmer
{
    /**
     * {@inheritdoc}
     */
    protected function writeCacheFile($file, $content)
    {
        // Dynamic paths
        $content = str_replace("'".DP_APP_DIR, "DP_APP_DIR.'", $content);

        return parent::writeCacheFile($file, $content);
    }
}
