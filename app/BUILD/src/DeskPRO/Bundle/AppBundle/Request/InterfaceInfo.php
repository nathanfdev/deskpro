<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2015, DeskPRO Ltd.
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

namespace DeskPRO\Bundle\AppBundle\Request;

use DeskPRO\Component\Util\ListUtils;

class InterfaceInfo
{
    const ID_INSTALLER = 'installer';
    const ID_ADMIN     = 'admin';
    const ID_AGENT     = 'agent';
    const ID_USER      = 'user';
    const ID_API       = 'api';
    const ID_CRON      = 'cron';
    const ID_CMD       = 'cmd';

    /**
     * @var string
     */
    private $interface_id;

    /**
     * InterfaceInfo constructor.
     *
     * @param string $interface
     */
    public function __construct($interface)
    {
        $this->interface_id = $interface;
    }

    /**
     * @return InterfaceInfo
     */
    public static function create()
    {
        if (defined('DP_INTERFACE')) {
            return new self(DP_INTERFACE);
        } else {
            return new self(self::ID_CMD);
        }
    }

    /**
     * Returns the current interface: admin, agent, install, user.
     */
    public function getInterfaceId()
    {
        return $this->interface_id;
    }

    /**
     * Check if the interface is $check, which can be one or more things to check.
     *
     * @param string|array $check
     *
     * @return bool
     */
    public function isInterfaceId($check)
    {
        if (!is_array($check)) {
            $check = [$check];
        }

        return ListUtils::contains($check, $this->interface_id);
    }

    /**
     * @return bool
     */
    public function isUserInterface()
    {
        return $this->interface_id === self::ID_USER;
    }

    /**
     * @return bool
     */
    public function isAgentInterface()
    {
        return $this->interface_id === self::ID_AGENT;
    }

    /**
     * @return bool
     */
    public function isAdminInterface()
    {
        return $this->interface_id === self::ID_ADMIN;
    }

    /**
     * @return bool
     */
    public function isApiInterface()
    {
        return $this->interface_id === self::ID_API;
    }

    /**
     * @return bool
     */
    public function isCronInterface()
    {
        return $this->interface_id === self::ID_CRON;
    }

    /**
     * @return bool
     */
    public function isCommandInterface()
    {
        return $this->interface_id === self::ID_CMD;
    }

    /**
     * @return bool
     */
    public function isCliInterface()
    {
        return $this->interface_id === self::ID_CMD || $this->interface_id === self::ID_CRON;
    }
}
