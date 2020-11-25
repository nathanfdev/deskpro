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
     * @var string
     */
    private $directory;

    /**
     * {@inheritDoc}
     */
    public function __construct($dir)
    {
        parent::__construct($dir);
        $this->directory = $dir;
    }

    /**
     * {@inheritDoc}
     */
    public function putClassMetadataInCache(ClassMetadata $metadata)
    {
        if (!defined('DPC_IS_READ_ONLY_FS')) {
            parent::putClassMetadataInCache($metadata);

            return;
        }

        if (!is_writable($this->directory)) {
            throw new \InvalidArgumentException(sprintf('The directory "%s" is not writable.', $this->directory));
        }

        $path = $this->directory.'/'.strtr($metadata->name, '\\', '-').'.cache.php';

        $tmpFile = @tempnam($this->directory, 'metadata-cache');
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
