<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\CacheWarmer;

class TemplatePathsCacheWarmer extends \Symfony\Bundle\FrameworkBundle\CacheWarmer\TemplatePathsCacheWarmer
{
    protected function writeCacheFile($file, $content)
    {
        // Re-write absolute paths to use DP_ROOT instead
        $content = str_replace("'".DP_ROOT, 'DP_ROOT.\'', $content);

        return parent::writeCacheFile($file, $content);
    }
}
