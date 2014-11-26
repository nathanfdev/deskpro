<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. http://www.deskpro.com/   |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2012, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at http://www.deskpro.com/license                           |
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
 * @subpackage DependencyInection
 */

namespace Application\DeskPRO\DependencyInjection\SystemServices;

use Application\DeskPRO\Auth\AuthenticationManager;
use Application\DeskPRO\DependencyInjection\DeskproContainer;

class AuthenticationManagerService
{
    public static function create(DeskproContainer $container)
    {
        /** @var \Application\DeskPRO\Usersource\UsersourceManager $um */
        $um = $container->getSystemService('usersource_manager');

        /** @var \Application\DeskPRO\Auth\AuthSettings $as */
        $as = $container->getSystemService('auth_settings');

        /** @var \Application\DeskPRO\Usersource\UsersourceAuthAdapterFactory $as */
        $aaf = $container->getSystemService('usersource_auth_adapter_factory');

        /** @var \Application\DeskPRO\Settings\Settings $as */
        $app_settings = $container->getSettingsHandler();

        return new AuthenticationManager(
            $as,
            $um,
            $aaf,
            $app_settings,
            DP_INTERFACE == 'user' ? 'user' : 'agent'
        );
    }
}
