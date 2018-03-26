<?php

/**
 * DeskPRO.
 */

namespace DpBehat;

use Behat\Symfony2Extension\Context\KernelAwareContext as KernelAwareContextInterface;
use Behatch\Context\BaseContext as BaseBehatContext;

/**
 * Class BaseContext.
 */
abstract class BaseContext extends BaseBehatContext implements KernelAwareContextInterface
{
    use KernelAwareTrait;

    public function resetAllContext()
    {
        $this->kernel()->shutdown();
        $this->kernel()->boot();
    }

    public function getTestRootDir()
    {
        return realpath(__DIR__.'/../..');
    }

    public function getTestDir($relativePath)
    {
        $root = $this->getTestRootDir();

        return realpath($root.'/'.ltrim($relativePath, '/'));
    }
}
