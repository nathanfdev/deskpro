<?php

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
