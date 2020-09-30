<?php

namespace Application\DeskPRO\Twig;

/**
 * Class FilesystemCache
 *
 * @package Application\DeskPRO\Twig
 */
class FilesystemCache extends \Twig\Cache\FilesystemCache
{
    public function write($key, $content)
    {
        if (!defined('DPC_IS_READ_ONLY_FS')) {
            parent::write($key, $content);

            return;
        }

        $dir = \dirname($key);
        if (!is_dir($dir)) {
            if (false === @mkdir($dir, 0777, true)) {
                clearstatcache(true, $dir);
                if (!is_dir($dir)) {
                    throw new \RuntimeException(sprintf('Unable to create the cache directory (%s).', $dir));
                }
            }
        }
        elseif (!is_writable($dir)) {
            throw new \RuntimeException(sprintf('Unable to write in the cache directory (%s).', $dir));
        }

        $tmpFile = @tempnam($dir, basename($key));

        if (false !== @file_put_contents($tmpFile, $content) && @copy($tmpFile, $key)) {
            @chmod($key, 0666 & ~umask());
            @unlink($tmpFile);

            $ref = (new \ReflectionObject($this))->getParentClass();
            $optionsRef = $ref->getProperty('options');
            $optionsRef->setAccessible(true);
            $options = $optionsRef->getValue($this);

            if (self::FORCE_BYTECODE_INVALIDATION == ($options & self::FORCE_BYTECODE_INVALIDATION)) {
                // Compile cached file into bytecode cache
                if (\function_exists('opcache_invalidate') && filter_var(ini_get('opcache.enable'), FILTER_VALIDATE_BOOLEAN)) {
                    @opcache_invalidate($key, true);
                } elseif (\function_exists('apc_compile_file')) {
                    apc_compile_file($key);
                }
            }

            return;
        }

        throw new \RuntimeException(sprintf('Failed to write cache file "%s".', $key));
    }
}
