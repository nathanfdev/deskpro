<?php

/**
 * DeskPRO.
 *
 * @category Apps
 */

namespace deskpro_us_active_directory\Usersource;

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

        $options = [];

        $options['port']                    = $settings->get('port');
        $options['host']                    = $settings->get('host');
        $options['baseDn']                  = $settings->get('base_dn');
        $options['username']                = $settings->get('service_username');
        $options['password']                = $settings->get('service_password');
        $options['accountDomainName']       = $settings->get('domain_name');
        $options['accountDomainNameShort']  = $settings->get('short_domain_name');
        $options['accountFilterFormat']     = $settings->get('filter');
        $options['disable_cert_validation'] = $settings->get('disable_cert_validation');
        $options['disableLdapPaging']       = $settings->get('disable_ldap_paging');
        $options['ldapPerPage']             = $settings->get('ldap_per_page');
        $options['raw_info_filter']         = $settings->get('raw_info_filter');

        switch ($settings->get('secure')) {
            case 'ssl':
                $options['useSsl']      = true;
                $options['useStartTls'] = false;
                break;
            case 'tls':
                $options['useStartTls'] = true;
                $options['useSsl']      = false;
                break;
            default:
                $options['useStartTls'] = false;
                $options['useSsl']      = false;

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
