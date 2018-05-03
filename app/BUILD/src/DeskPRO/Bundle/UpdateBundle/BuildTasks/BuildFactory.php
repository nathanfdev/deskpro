<?php

namespace DeskPRO\Bundle\UpdateBundle\BuildTasks;

use Application\InstallBundle\Upgrade\Build\AbstractBuild;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class BuildFactory
{
    /**
     * @var ManifestReader
     */
    private $manifestReader;
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * BuildFactory constructor.
     *
     * @param ManifestReader     $manifestReader
     * @param ContainerInterface $container
     * @param LoggerInterface    $logger
     */
    public function __construct(ManifestReader $manifestReader, ContainerInterface $container, LoggerInterface $logger)
    {
        $this->manifestReader = $manifestReader;
        $this->container      = $container;
        $this->logger         = $logger;
    }

    /**
     * @param int $buildId
     *
     * @return AbstractBuild
     */
    public function createBuild($buildId)
    {
        $class = $this->getBuildClass($buildId);

        return $this->makeBuildClass($class);
    }

    /**
     * @param string $buildClassName
     *
     * @return AbstractBuild
     */
    public function makeBuildClass($buildClassName)
    {
        return new $buildClassName($this->container, $this->logger);
    }

    /**
     * Get the build class for a build ID.
     *
     * @param int $buildId
     *
     * @throws \Exception
     *
     * @return string
     */
    public function getBuildClass($buildId)
    {
        $class = 'Application\\InstallBundle\\Upgrade\\Build\\Build'.$buildId;

        if (!class_exists($class, false)) {
            $buildInfo = $this->manifestReader->findBuild($buildId);
            if ($buildInfo) {
                $file  = DP_ROOT.$buildInfo['file'];
                $class = $buildInfo['classname'];
            } else {
                throw new \Exception("Unknown build. $buildId is not in the manifest.");
            }
            require_once $file;
        }

        return $class;
    }
}
