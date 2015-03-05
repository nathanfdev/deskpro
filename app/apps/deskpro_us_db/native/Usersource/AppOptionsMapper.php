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

namespace deskpro_us_db\Usersource;

use Application\DeskPRO\Entity\AppInstance;
use Orb\Util\OptionsArray;
use Orb\Util\Strings;

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

        $connection_options = array();
        $options = array();

        switch ($settings->get('db_type')) {
            case 'pdo_mysql':
                $connection_options['driver']   = 'pdo_mysql';
                $connection_options['user']     = $settings->get('db_username');
                $connection_options['password'] = $settings->get('db_password');
                $connection_options['dbname']   = $settings->get('db_name');
                $connection_options['host']     = $settings->get('db_host');
                $connection_options['port']     = $settings->get('db_port');

                if ($settings->get('db_port_type') == 'socket') {
                    unset($connection_options['port'], $connection_options['host']);
                    $connection_options['socket'] = $settings->get('db_socket');
                }
                break;
            case 'pdo_pgsql':
                $connection_options['driver']   = 'pdo_pgsql';
                $connection_options['user']     = $settings->get('db_username');
                $connection_options['password'] = $settings->get('db_password');
                $connection_options['dbname']   = $settings->get('db_name');
                $connection_options['host']     = $settings->get('db_host');
                $connection_options['port']     = $settings->get('db_port');
                break;
            case 'pdo_sqlite':
                $connection_options['driver']   = 'pdo_sqlite';
                $connection_options['user']     = $settings->get('db_username');
                $connection_options['password'] = $settings->get('db_password');
                $connection_options['path']     = $settings->get('path');
                $connection_options['host']     = $settings->get('db_host');
                $connection_options['port']     = $settings->get('db_port');
                break;
            case 'sqlsrv':
                $connection_options['driver']   = 'sqlsrv';
                $connection_options['user']     = $settings->get('db_username');
                $connection_options['password'] = $settings->get('db_password');
                $connection_options['dbname']   = $settings->get('db_name');
                $connection_options['host']     = $settings->get('db_host');
                $connection_options['port']     = $settings->get('db_port');
                break;
            case 'oci8':
                $connection_options['driver']   = 'oci8';
                $connection_options['user']     = $settings->get('db_username');
                $connection_options['password'] = $settings->get('db_password');
                $connection_options['dbname']   = $settings->get('db_name');
                $connection_options['host']     = $settings->get('db_host');
                $connection_options['port']     = $settings->get('db_port');

                if ($settings->get('db_service_name')) {
                    $connection_options['servicename'] = $settings->get('db_service_name');
                }
                break;
            case 'pdo_odbc':
                $options['db_custom_options'] = $settings->get('db_odbc_dsn', '');
                $odbc_settings = Strings::parseEqualsLines($settings->get('db_odbc_dsn'));

                $connection_options['driverClass'] = 'Orb\\Doctrine\\DBAL\\Driver\\PDOODBC\\SQLServerDriver';
                $connection_options['dsn'] = @$odbc_settings['dsn'];
                $connection_options['user'] = @$odbc_settings['user'];
                $connection_options['password'] = @$odbc_settings['password'];
                break;
        }

        if ($settings->get('db_with_options') && ($custom_settings = trim($settings->get('db_custom_options', '')))) {
            $options['db_custom_options'] = $settings->get('db_custom_options', '');
            $custom_settings = Strings::parseEqualsLines($custom_settings);
            if ($custom_settings) {
                $connection_options['driverOptions'] = $custom_settings;
            }
        }

        $options['connection_options'] = $connection_options;

        foreach (array('table', 'field_id', 'field_username', 'field_email', 'field_password', 'field_first_name', 'field_last_name', 'field_name') as $f) {
            $options[$f] = $settings->get($f, '');
        }

        $options['password_php'] = $settings->get('php_code');

        return $options;
    }
}
