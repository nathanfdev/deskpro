<?php

/**
 * DeskPRO.
 *
 * @category Apps
 */

namespace deskpro_us_phpbb\Usersource;

use Application\DeskPRO\Entity\AppInstance;
use Orb\Util\OptionsArray;

class AppOptionsMapper
{
    /**
     * @param array|AppInstance $app_or_settings
     *
     * @throws \InvalidArgumentException
     *
     * @return array
     */
    public static function getOptions($app_or_settings)
    {
        if ($app_or_settings instanceof AppInstance) {
            $settings = $app_or_settings->getSettings();
        } else {
            if (!is_array($app_or_settings)) {
                throw new \InvalidArgumentException();
            }
            $settings = $app_or_settings;
        }

        $settings = new OptionsArray($settings);

        $options                    = [];
        $options['db_dsn']          = $settings->get('db_dsn');
        $options['db_username']     = $settings->get('db_username');
        $options['db_password']     = $settings->get('db_password');
        $options['table_prefix']    = $settings->get('table_prefix');
        $options['raw_info_filter'] = $settings->get('raw_info_filter');

        if ($settings->get('phpbb_version') == '3') {
            $options['check_service_url'] = $settings->get('check_service_url', '');
            $options['check_service_key'] = $settings->get('check_service_key', 'dp_login_check');
        }

        return $options;
    }
}
