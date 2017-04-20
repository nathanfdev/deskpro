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

namespace DeskPRO\Bundle\AppBundle\Settings\Model;

use JMS\Serializer\Annotation as JMS;
use Orb\Util\Dates;

class UpdaterStatus
{
    /**
     * @var \DateTime
     * @JMS\Type("DateTime")
     */
    private $nextCheck;

    /**
     * @var bool
     * @JMS\Type("boolean")
     */
    private $nextIsManual;

    /**
     * @var string
     * @JMS\Type("string")
     */
    private $cliCommand;

    /**
     * @var string
     * @JMS\Type("string")
     */
    private $backupPath;

    /**
     * @var string
     * @JMS\Type("string")
     */
    private $logUrl;

    /**
     * @var string
     * @JMS\Type("string")
     */
    private $watcherUrl;

    /**
     * @return \DateTime
     */
    public function getNextCheck()
    {
        return $this->nextCheck;
    }

    /**
     * Is the next update scheduled manually?
     *
     * @return bool
     */
    public function isNextManual()
    {
        return $this->nextIsManual;
    }

    /**
     * @JMS\VirtualProperty()
     *
     * @return null|string
     */
    public function getNextCheckDesc()
    {
        if (!$this->nextCheck) {
            return;
        }

        if ($this->nextCheck < (new \DateTime())) {
            return 'a few seconds';
        }

        return Dates::secsToReadable($this->nextCheck->getTimestamp() - time());
    }

    /**
     * @param \DateTime $nextCheck
     * @param bool      $isManual
     *
     * @return $this
     */
    public function setNextCheck(\DateTime $nextCheck = null, $isManual = false)
    {
        $this->nextCheck    = $nextCheck;
        $this->nextIsManual = $isManual;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getCliCommand()
    {
        return $this->cliCommand;
    }

    /**
     * @param string $cliCommand
     *
     * @return $this
     */
    public function setCliCommand($cliCommand)
    {
        $this->cliCommand = $cliCommand;

        return $this;
    }

    /**
     * @return string
     */
    public function getBackupPath()
    {
        return $this->backupPath;
    }

    /**
     * @param string $backupPath
     *
     * @return $this
     */
    public function setBackupPath($backupPath)
    {
        $this->backupPath = $backupPath;

        return $this;
    }

    /**
     * @return string
     */
    public function getLogUrl()
    {
        return $this->logUrl;
    }

    /**
     * @param string $logUrl
     *
     * @return $this
     */
    public function setLogUrl($logUrl)
    {
        $this->logUrl = $logUrl;

        return $this;
    }

    /**
     * @return string
     */
    public function getWatcherUrl()
    {
        return $this->watcherUrl;
    }

    /**
     * @param string $watcherUrl
     *
     * @return $this
     */
    public function setWatcherUrl($watcherUrl)
    {
        $this->watcherUrl = $watcherUrl;

        return $this;
    }
}
