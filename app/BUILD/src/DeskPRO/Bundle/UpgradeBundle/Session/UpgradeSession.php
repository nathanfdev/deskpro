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

namespace DeskPRO\Bundle\UpgradeBundle\Session;

use DeskPRO\Bundle\UpgradeBundle\Session\SessionStep\SessionStep;
use DeskPRO\Bundle\UpgradeBundle\Session\SessionStep\StatusStep;

/**
 * This keeps track of the current status of an automatic upgrade process.
 *
 * An automatic upgrade process goes from start to finish to get latest updates,
 * download, extract files, runs req check, and then finally activates and upgrades the db.
 *
 * We make use of lots of logging in each of the various upgrade processes. We have a special
 * handler that interprets log lines and keeps an upgrade "session" file up to date. The session
 * file can be read by some other process (e.g. web interface) to show the status of an upgrade.
 */
class UpgradeSession extends SessionStep
{
    const STEP_STATUS          = 'status';
    const STEP_DOWNLOAD_DISTRO = 'download_distro';
    const STEP_EXTRACT_DISTRO  = 'extract_distro';
    const STEP_BACKUP          = 'backup';
    const STEP_REQ_CHECK       = 'activate_req_check';
    const STEP_DISABLE_SITE    = 'activate_disable';
    const STEP_UPGRADE         = 'activate_upgrade';
    const STEP_ENABLE_SITE     = 'activate_enable';

    /**
     * @var SessionStep[]
     */
    private $steps;

    public function __construct()
    {
        $this->steps = [
            self::STEP_STATUS          => new StatusStep(),
            self::STEP_DOWNLOAD_DISTRO => new SessionStep(),
            self::STEP_EXTRACT_DISTRO  => new SessionStep(),
            self::STEP_BACKUP          => new SessionStep(),
            self::STEP_REQ_CHECK       => new SessionStep(),
            self::STEP_DISABLE_SITE    => new SessionStep(),
            self::STEP_UPGRADE         => new SessionStep(),
            self::STEP_ENABLE_SITE     => new SessionStep(),
        ];
    }

    /**
     * @return StatusStep
     */
    public function getStatusStep()
    {
        return $this->getStep(self::STEP_STATUS);
    }

    /**
     * @param string $id
     *
     * @return SessionStep
     */
    public function getStep($id)
    {
        if (!isset($this->steps[$id])) {
            throw new \InvalidArgumentException();
        }

        return $this->steps[$id];
    }

    /**
     * @return array
     */
    public function getStepIds()
    {
        return array_keys($this->steps);
    }

    /**
     * Gets the current step. This will be the one currently waiting to run,
     * currently running, or the one that failed.
     *
     * If the installer is not running, then this will return null;
     */
    public function findCurrentStepId()
    {
        if (!$this->isRunning()) {
            return;
        }

        foreach ($this->steps as $stepId => $step) {
            if ($step->isFinished()) {
                if ($step->isError()) {
                    return $stepId;
                }
            } else {
                return $stepId;
            }
        }

        return;
    }

    /**
     * {@inheritdoc}
     */
    public function jsonSerialize()
    {
        $dat          = parent::jsonSerialize();
        $dat['steps'] = [];

        foreach ($this->steps as $stepId => $step) {
            $dat['steps'][$stepId] = $step->jsonSerialize();
        }

        return $dat;
    }

    /**
     * {@inheritdoc}
     */
    public function merge(UpgradeSession $session)
    {
        parent::merge($session);

        foreach ($session->getStepIds() as $stepId) {
            $theirStep = $session->getStep($stepId);
            $myStep    = $this->getStep($stepId);
            $myStep->merge($theirStep);
        }
    }
}
