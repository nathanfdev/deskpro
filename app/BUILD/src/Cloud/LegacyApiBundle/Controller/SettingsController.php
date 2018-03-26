<?php

/**
 * DeskPRO.
 */

namespace Cloud\LegacyApiBundle\Controller;

use Application\DeskPRO\Entity\BrandSetting;
use Application\LegacyApiBundle\Controller\SettingsController as BaseSettingsController;
use Cloud\LegacyApiBundle\Helper\CloudBrandHelper;
use Orb\Util\OptionsArray;

class SettingsController extends BaseSettingsController
{
    //###################################################################################################################
    // get-url-settings
    //###################################################################################################################

    public function getUrlSettingsAction()
    {
        $settings = [
            // The custom domain being used, if any
            'cloud_custom_domain' => $this->settings->get('core.cloud_custom_domain') ?: null,

            // The custom domain that we have configured with a custom cert
            'cloud_custom_domain_ssl' => $this->settings->get('core.cloud_custom_domain_ssl') ? true : false,

            // If the URL should be https or not
            'cloud_url_ssl' => $this->settings->get('core.cloud_url_ssl') ? true : false,

            'deskpro_url_autocorrect' => $this->settings->get('core.deskpro_url_autocorrect') ? true : false,
        ];

        $settings['domain_choice'] = 'default';
        if ($settings['cloud_custom_domain']) {
            $settings['domain_choice'] = 'custom';
        }

        return $this->createApiResponse(['settings' => $settings]);
    }

    //###################################################################################################################
    // save-url-settings
    //###################################################################################################################

    public function saveUrlSettingsAction()
    {
        $in_settings  = new OptionsArray($this->in->getArrayValue('settings'));
        $set_settings = [
            'core.deskpro_url_autocorrect' => $in_settings->get('deskpro_url_autocorrect', false),
        ];

        if ($in_settings->get('domain_choice') == 'custom') {
            $domain = preg_replace('#^https?://#', '', strtolower($in_settings->get('cloud_custom_domain')));
            $domain = trim($domain, '/');

            $url_test = 'http://'.$domain.'/';
            $url_bits = @parse_url($url_test);

            if (empty($url_bits['host']) || strpos($url_bits['host'], 'deskpro.com') !== false || $url_bits['host'] != $domain) {
                return $this->createApiErrorResponse('invalid_custom_domain', 'The domain you entered appears to be invalid');
            }

            $set_settings['core.cloud_custom_domain'] = $domain;

            if ($in_settings->get('cloud_url_ssl') && $this->settings->get('core.cloud_custom_domain_ssl') == $domain) {
                $set_settings['core.cloud_url_ssl'] = true;
            } else {
                $set_settings['core.cloud_url_ssl'] = false;
            }

            if ($set_settings['core.cloud_url_ssl']) {
                $url = 'https://'.$domain.'/';
            } else {
                $url = 'http://'.$domain.'/';
            }
            $set_settings['core.deskpro_url'] = $url;

            if ($domain != $this->settings->get('core.cloud_custom_domain')) {
                CloudBrandHelper::flushBrandDomains();
            }
        } else {
            $set_settings['core.cloud_custom_domain'] = null;

            $set_settings['core.cloud_url_ssl'] = (bool) $in_settings->get('cloud_url_ssl');
            if ($set_settings['core.cloud_url_ssl']) {
                $url = 'https://'.DPC_SITE_DOMAIN.'/';
            } else {
                $url = 'http://'.DPC_SITE_DOMAIN.'/';
            }
            $set_settings['core.deskpro_url'] = $url;
        }

        foreach ($set_settings as $k => $v) {
            $this->settings->setSetting($k, $v);
        }

        // primary brand url needs to change too
        $brandStack   = $this->get('brand_stack');
        $primaryBrand = $brandStack->getDefaultBrand();
        $repos        = $this->get('doctrine.orm.default_entity_manager')->getRepository(BrandSetting::class);
        $repos->updateSetting('core.deskpro_url', $set_settings['core.deskpro_url'], $primaryBrand);

        CloudBrandHelper::flushBrandDomains();

        return $this->createApiSuccessResponse();
    }
}
