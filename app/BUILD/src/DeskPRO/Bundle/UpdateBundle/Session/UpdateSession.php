<?php

namespace DeskPRO\Bundle\UpdateBundle\Session;

use DeskPRO\Bundle\UpdateBundle\Session\SessionStep\SessionStep;
use DeskPRO\Bundle\UpdateBundle\Session\SessionStep\StatusStep;

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
class UpdateSession extends SessionStep
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
        parent::__construct('Updater');

        $this->steps = [
            self::STEP_STATUS          => new StatusStep('Check For Updates'),
            self::STEP_DOWNLOAD_DISTRO => new SessionStep('Download Updates'),
            self::STEP_EXTRACT_DISTRO  => new SessionStep('Extract Updates'),
            self::STEP_BACKUP          => new SessionStep('Backup Database'),
            self::STEP_REQ_CHECK       => new SessionStep('Check Requirements'),
            self::STEP_DISABLE_SITE    => new SessionStep('Disable Helpdesk'),
            self::STEP_UPGRADE         => new SessionStep('Upgrade Helpdesk'),
            self::STEP_ENABLE_SITE     => new SessionStep('Re-enable Helpdesk'),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function finished($summaryText, $details = '')
    {
        foreach ($this->steps as $step) {
            if ($step->isRunning()) {
                $step->finished('Updater process finished');
            }
        }

        return parent::finished($summaryText, $details);
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

        $nextIsUp = false;
        foreach ($this->steps as $stepId => $step) {
            if ($step->isFinished()) {
                if ($step->isError()) {
                    return $stepId;
                }
                $nextIsUp = true;
            } elseif ($step->isRunning()) {
                return $stepId;
            } elseif ($nextIsUp) {
                return $stepId;
            }
        }

        return;
    }

    /**
     * @return $this
     */
    public function touch()
    {
        $this->data['lastWrite'] = date('Y-m-d H:i:s');
        $this->data['tick']      = microtime(true);

        return $this;
    }

    /**
     * @return int
     */
    public function getTick()
    {
        return $this->data['tick'] ?: microtime(true);
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
    public function merge(SessionStep $session)
    {
        parent::merge($session);

        if ($session instanceof self) {
            foreach ($session->getStepIds() as $stepId) {
                $theirStep = $session->getStep($stepId);
                $myStep    = $this->getStep($stepId);
                $myStep->merge($theirStep);
            }
        }
    }
}
