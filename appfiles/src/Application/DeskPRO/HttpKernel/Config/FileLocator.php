<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage HttpKernel
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\DeskPRO\HttpKernel\Config;

use Symfony\Component\Config\FileLocator as BaseFileLocator;
use Symfony\Component\HttpKernel\KernelInterface;

class FileLocator extends BaseFileLocator
{
    private $kernel;

    /**
     * Constructor.
     *
     * @param KernelInterface $kernel A KernelInterface instance
     * @param string|array    $paths  A path or an array of paths where to look for resources
     */
    public function __construct(KernelInterface $kernel, array $paths = array())
    {
        $this->kernel = $kernel;

        parent::__construct($paths);
    }

    /**
     * {@inheritdoc}
     */
    public function locate($file, $currentPath = null, $first = true)
    {
        if ('@' === $file[0]) {
            return $this->kernel->locateResource($file, $currentPath, $first);
        }

		$file = str_replace('%DP_ROOT%', DP_ROOT, $file);

        return parent::locate($file, $currentPath, $first);
    }
}
