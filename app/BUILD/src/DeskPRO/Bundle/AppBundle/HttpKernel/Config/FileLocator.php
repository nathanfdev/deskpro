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

    /** @var null we don't use this, but it's in the base class, so it might be useful later  */
    private $path;

    public function __construct(KernelInterface $kernel, $path = null, array $paths = array())
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
        if ('@' === $file[0]) {

            /*
             * If a bundle is only in SOME kernels, we need to be explicit here about how
             * to locate the file. If the bundle is included in ALL of our kernels we do not
             * need to be explicit, because the kernel will find it by itself.
             */

            if (!$currentPath and strpos($file, '@TwigBundle') === 0) {
                // this may not be necessarly, but leaving it because it was in the old
                // Application\DeskPRO FileLocator class, so it's probably used somewhere
                $currentPath = DP_ROOT.'/sys/Resources';
            }

            return $this->kernel->locateResource($file, $currentPath, $first);
        }

        $file = str_replace('%DP_ROOT%', DP_ROOT, $file);

        return parent::locate($file, $currentPath, $first);
    }
}
