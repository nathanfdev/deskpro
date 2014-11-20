<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/  |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2014, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at https://www.deskpro.com/eula/                            |
|                                                                          |
| By using this software, you acknowledge having read the license          |
| and agree to be bound thereby.                                           |
|                                                                          |
| Please note that DeskPRO is not free software. We release the full       |
| source code for our software because we trust our users to pay us for    |
| the huge investment in time and energy that has gone into both creating  |
| this software and supporting our customers. By providing the source code |
| we preserve our customers' ability to modify, audit and learn from our   |
| work. We have been developing DeskPRO since 2001, please help us make it |
| another decade.                                                          |
|                                                                          |
| Like the work you see? Think you could make it better? We are always     |
| looking for great developers to join us: http://www.deskpro.com/jobs/    |
|                                                                          |
| ~ Thanks, Everyone at Team DeskPRO                                       |
\**************************************************************************/

/**
 * DeskPRO
 *
 * @package DeskPRO
 * @category Entities
 */

namespace Application\ApiBundle\PermissionStrategy;

use Application\ApiBundle\ApiUser;

class MultiPermissions implements PermissionStrategyInterface
{
    /**
     * Array of type => array(PermissionStrategyInterface)
     * @var array
     */
    private $perms = array();

    /**
     * @var Callback
     */
    private $fn = null;

    /**
     * @param null|Callback $fn Optionally a callback that returns the type name to apply for a check
     */
    public function __construct($fn = null)
    {
        $this->fn = $fn;
    }

    /**
     * @param PermissionStrategyInterface $p
     * @param string                      $type
     */
    public function addPermissionStrategy(PermissionStrategyInterface $p, $type = 'default')
    {
        if (!isset($this->perms[$type])) {
            $this->perms[$type] = array();
        }
        $this->perms[$type][] = $p;
    }

    /**
     * {@inheritDoc}
     */
    public function userHasPermission(ApiUser $api_user, $context_info = null)
    {
        if ($this->fn) {
            $type = $this->fn($context_info);
        } else {
            if (!empty($context_info['type'])) {
                $type = $context_info['type'];
            } else {
                $type = 'default';
            }
        }

        if (!isset($this->perms[$type])) {
            return true;
        }

        foreach ($this->perms[$type] as $p) {
            if (!$p->userHasPermission($api_user, $context_info)) {
                return false;
            }
        }

        return true;
    }
}
