<?php

namespace Application\DeskPRO\Serializer;

use Metadata\ClassMetadata;

/**
 * Class FileCache
 *
 * @package Application\DeskPRO\Serializer
 */
class FileCache extends \Metadata\Cache\FileCache
{
    /**
     * {@inheritDoc}
     */
    public function putClassMetadataInCache(ClassMetadata $metadata)
    {
        if (!defined('DPC_IS_CLOUD')) {
            parent::putClassMetadataInCache($metadata);

            return;
        }

        $thisRef = (new \ReflectionObject($this))->getParentClass();
        $dirRef  = $thisRef->getProperty('dir');
        $dirRef->setAccessible(true);

        $dir = $dirRef->getValue($this);

        if (!is_writable($dir)) {
            throw new \InvalidArgumentException(sprintf('The directory "%s" is not writable.', $dir));
        }

        $path = $dir.'/'.strtr($metadata->name, '\\', '-').'.cache.php';

        $tmpFile = @tempnam($dir, 'metadata-cache');
        file_put_contents($tmpFile, '<?php return unserialize('.var_export(serialize($metadata), true).');');

        // Let's not break filesystems which do not support chmod.
        @chmod($tmpFile, 0666 & ~umask());

        $this->copyFile($tmpFile, $path);
    }

    /**
     * Renames a file with fallback for windows
     *
     * @param string $source
     * @param string $target
     */
    private function copyFile($source, $target) {
        if (@copy($source, $target)) {
            @unlink($source);
        }
    }
}
