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
            'cloud_custom_domain' => $this->settings->get('core.cloud_custom_domain') ?: null,
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
            'core.deskpro_url_autocorrect' => false,
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

            $url                              = 'https://'.$domain.'/';
            $set_settings['core.deskpro_url'] = $url;

            if ($domain != $this->settings->get('core.cloud_custom_domain')) {
                CloudBrandHelper::flushBrandDomains();
            }
        } else {
            $set_settings['core.cloud_custom_domain'] = null;
            $set_settings['core.deskpro_url']         = 'https://'.DPC_SITE_DOMAIN.'/';
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

    //###################################################################################################################
    // provision
    //###################################################################################################################

    public function setupCustomDomainAction()
    {
        $domain = $this->in->getString('domain');
        $domain = preg_replace('#^https?://#', '', strtolower($domain));
        $domain = trim($domain, '/');

        $url_test = 'http://'.$domain.'/';
        $url_bits = @parse_url($url_test);

        if (empty($url_bits['host']) || strpos($url_bits['host'], 'deskpro.com') !== false || $url_bits['host'] != $domain) {
            return $this->createJsonResponse([
                'error'   => true,
                'type'    => 'ma',
                'code'    => 'domain.invalid',
                'message' => 'Invalid domain name',
            ]);
        }

        return $this->createJsonResponse(CloudBrandHelper::provisionCustomDomain($domain));
    }
}
