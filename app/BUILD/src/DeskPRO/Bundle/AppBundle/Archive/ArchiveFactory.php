<?php

namespace DeskPRO\Bundle\AppBundle\Archive;

use DeskPRO\Bundle\AppBundle\AppEnv\AppEnvInterface;

class ArchiveFactory
{
    /**
     * @var AppEnvInterface
     */
    private $environment;

    /**
     * ArchiveFactory constructor.
     *
     * @param AppEnvInterface $environment
     */
    public function __construct(AppEnvInterface $environment)
    {
        $this->environment = $environment;
    }

    public function createZipArchive()
    {
        return new Zip($this->environment);
    }
}
