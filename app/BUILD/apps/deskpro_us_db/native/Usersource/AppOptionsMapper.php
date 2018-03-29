<?php

/**
 * DeskPRO.
 *
 * @category Apps
 */

namespace deskpro_us_db\Usersource;

use Application\DeskPRO\Entity\AppInstance;
use Orb\Util\OptionsArray;
use Orb\Util\Strings;

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

        $connection_options = [];
        $options            = [];

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
                $odbc_settings                = Strings::parseEqualsLines($settings->get('db_odbc_dsn'));

                $connection_options['driverClass'] = 'Orb\\Doctrine\\DBAL\\Driver\\PDOODBC\\SQLServerDriver';
                $connection_options['dsn']         = @$odbc_settings['dsn'];
                $connection_options['user']        = @$odbc_settings['user'];
                $connection_options['password']    = @$odbc_settings['password'];
                break;
        }

        if ($settings->get('db_with_options') && ($custom_settings = trim($settings->get('db_custom_options', '')))) {
            $options['db_custom_options'] = $settings->get('db_custom_options', '');
            $custom_settings              = Strings::parseEqualsLines($custom_settings);
            if ($custom_settings) {
                $connection_options['driverOptions'] = $custom_settings;
            }
        }

        $options['connection_options'] = $connection_options;

        foreach (['table', 'field_id', 'field_username', 'field_email', 'field_password', 'field_first_name', 'field_last_name', 'field_name'] as $f) {
            $options[$f] = $settings->get($f, '');
        }

        $options['password_php']    = $settings->get('php_code');
        $options['raw_info_filter'] = $settings->get('raw_info_filter');

        return $options;
    }
}
