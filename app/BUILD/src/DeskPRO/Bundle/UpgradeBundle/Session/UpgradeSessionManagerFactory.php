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

class UpgradeSessionManagerFactory
{
    /**
     * @var string
     */
    private $sessionId;

    /**
     * @var UpgradeSessionManager
     */
    private $sessionManager;

    /**
     * @var string
     */
    private $tmpPath;

    /**
     * UpgradeSessionManagerFactory constructor.
     *
     * @param string $tmpPath
     */
    public function __construct($tmpPath)
    {
        $this->tmpPath = $tmpPath;
    }

    /**
     * @param string $sessionId
     */
    public function enableSessionId($sessionId)
    {
        if ($this->sessionId) {
            throw new \RuntimeException('Session has already been set');
        }
        $this->sessionId = $sessionId;
    }

    /**
     * @return bool
     */
    public function isEnabled()
    {
        return $this->sessionId !== null;
    }

    /**
     * @return UpgradeSessionManager
     */
    public function getManager()
    {
        if (!$this->sessionId) {
            throw new \RuntimeException('No session has been started');
        }

        if (!$this->sessionManager) {
            $this->sessionManager = new UpgradeSessionManager($this->sessionId, $this->tmpPath);
        }

        return $this->sessionManager;
    }
}
