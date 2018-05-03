<?php

namespace DeskPRO\Bundle\UpdateBundle\BuildActivate\HelpdeskState;

use DeskPRO\Bundle\UpdateBundle\Instance\BuildInstance;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

class HelpdeskStateModifier implements HelpdeskStateModifierInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;

    const OFFLINE_TRIGGER_NAME  = 'helpdesk-offline';
    const ACTIVE_BUILD_FILENAME = 'active_build.txt';

    /**
     * @var \DpRun\DpEnv
     */
    private $dpEnv;

    /**
     * HelpdeskStateModifier constructor.
     *
     * @param \DpRun\DpEnv $dpEnv
     */
    public function __construct(\DpRun\DpEnv $dpEnv, LoggerInterface $logger = null)
    {
        $this->dpEnv = $dpEnv;
        $this->setLogger($logger ?: new NullLogger());
    }

    /**
     * {@inheritdoc}
     */
    public function disableHelpdeskForUpdate()
    {
        if (!$this->dpEnv->getDatManager()->enableTrigger(self::OFFLINE_TRIGGER_NAME)) {
            $this->logger->error('[HelpdeskStateModifier] failed to set helpdesk-offline trigger file');
            throw new HelpdeskStateException('Failed to set helpdesk-offline trigger file. Helpdesk cannot be turned off.', HelpdeskStateException::SET_OFFLINE_FAILED);
        }

        $this->logger->info('[HelpdeskStateModifier] successfully created helpdesk-offline trigger file');
    }

    /**
     * {@inheritdoc}
     */
    public function enableHelpdeskFromUpdate()
    {
        if (
            $this->dpEnv->getDatManager()->hasTrigger(self::OFFLINE_TRIGGER_NAME)
            && !$this->dpEnv->getDatManager()->disableTrigger(self::OFFLINE_TRIGGER_NAME)
        ) {
            $this->logger->error('[HelpdeskStateModifier] failed to delete helpdesk-offline trigger file');
            throw new HelpdeskStateException('Failed to unset helpdesk-offline trigger file. Helpdesk is stuck in offline mode.', HelpdeskStateException::SET_OFFLINE_FAILED);
        }

        $this->logger->info('[HelpdeskStateModifier] successfully deleted helpdesk-offline trigger file');
    }

    /**
     * {@inheritdoc}
     */
    public function setHelpdeskBuild(BuildInstance $build)
    {
        $filepath = $this->dpEnv->getUserCacheDir().DIRECTORY_SEPARATOR.self::ACTIVE_BUILD_FILENAME;

        // We can try unlinking (means next web request will attempt to calculate the build itself)
        // or we can try just setting the actual build
        // Only if both fail does this step fail

        if (!@unlink($filepath)) {
            $this->logger->info('[HelpdeskStateModifier] failed to delete active build file: '.$filepath);
        }

        if (!@file_put_contents($filepath, $build->getBuildId())) {
            $this->logger->error('[HelpdeskStateModifier] failed to write active build file: '.$filepath);

            throw new HelpdeskStateException('Failed to update the active build ID file. The helpdesk is stuck using the previous build.', HelpdeskStateException::SET_BUILD_FAILED);
        }

        $this->logger->info('[HelpdeskStateModifier] successfully reset active build file: '.$filepath);
    }
}
