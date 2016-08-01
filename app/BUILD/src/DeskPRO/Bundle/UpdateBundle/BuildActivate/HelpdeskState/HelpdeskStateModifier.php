<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2016, DeskPRO Ltd.
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
