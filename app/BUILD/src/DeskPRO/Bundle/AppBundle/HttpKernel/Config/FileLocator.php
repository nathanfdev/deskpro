<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\AppBundle\HttpKernel\Config;

use Symfony\Component\Config\FileLocator as BaseFileLocator;
use Symfony\Component\HttpKernel\KernelInterface;

class FileLocator extends BaseFileLocator
{
    /** @var \Symfony\Component\HttpKernel\KernelInterface */
    private $kernel;

    /** @var null we don't use this, but it's in the base class, so it might be useful later */
    private $path;

    public function __construct(KernelInterface $kernel, $path = null, array $paths = [])
    {
        $this->kernel = $kernel;
        if (null !== $path) {
            $this->path = $path;
            $paths[]    = $path;
        }

        parent::__construct($paths);
    }

    /**
     * {@inheritdoc}
     */
    public function locate($file, $currentPath = null, $first = true)
    {
        if (isset($file[0]) && '@' === $file[0]) {
            return $this->kernel->locateResource($file, $this->path, $first);
        }

        $file = str_replace('%DP_ROOT%', DP_ROOT, $file);

        return parent::locate($file, $currentPath, $first);
    }
}
