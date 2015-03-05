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
 * @category Apps
 */

namespace deskpro_us_ldap\Usersource;

use Application\DeskPRO\Entity\AppInstance;
use Orb\Util\OptionsArray;

class AppOptionsMapper
{
    /**
     * @param  array|AppInstance         $app_or_settings
     * @return array
     * @throws \InvalidArgumentException
     */
    public static function getOptions($app_or_settings)
    {
        if ($app_or_settings instanceof AppInstance) {
            $settings = $app_or_settings->getSettings();
        } else {
            if (!is_array($app_or_settings)) {
                throw new \InvalidArgumentException;
            }
            $settings = $app_or_settings;
        }

        $settings = new OptionsArray($settings);

        $options = array();

        $options['port']                   = $settings->get('port');
        $options['host']                   = $settings->get('host');
        $options['baseDn']                 = $settings->get('base_dn');
        $options['username']               = $settings->get('service_username');
        $options['password']               = $settings->get('service_password');
        $options['accountFilterFormat']    = $settings->get('filter');

        switch ($settings->get('secure')) {
            case 'ssl':
                $options['useSsl'] = true;
                break;
            case 'tls':
                $options['useStartTls'] = true;
                break;
        }

        if (!$options['port']) {
            if (isset($options['useSsl'])) {
                $options['port'] = 636;
            } else {
                $options['port'] = 389;
            }
        }

        return $options;
    }
}
