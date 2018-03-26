<?php

namespace DeskPRO\Bundle\SendmailBundle\Templating;

use Symfony\Bundle\FrameworkBundle\Templating\TemplateReference;
use Symfony\Component\Config\FileLocator as BaseFileLocator;
use Symfony\Component\HttpKernel\KernelInterface;

class FileLocator extends BaseFileLocator
{
    /** @var \Symfony\Component\HttpKernel\KernelInterface */
    private $kernel;

    public function __construct(KernelInterface $kernel, $path = null, array $paths = [])
    {
        $this->kernel = $kernel;

        parent::__construct($paths);
    }

    /**
     * {@inheritdoc}
     */
    public function locate($file, $currentPath = null, $first = true)
    {
        if ($file instanceof TemplateReference) {
            return $this->kernel->locateResource($file->getPath());
        }

        if (is_string($file) && '@' === $file[0]) {
            if (!$currentPath and strpos($file, '@TwigBundle') === 0) {
                $currentPath = DP_ROOT.'/sys/Resources';
            }

            return $this->kernel->locateResource($file, $currentPath, $first);
        }

        $file = str_replace('%DP_ROOT%', DP_ROOT, $file);

        return parent::locate($file, $currentPath, $first);
    }
}
