<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2017, DeskPRO Ltd.
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
