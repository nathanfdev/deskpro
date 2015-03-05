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
 * @subpackage ApiBundle
 */

namespace Cloud\ApiBundle\Controller;

use Application\ApiBundle\Controller\SettingsController as BaseSettingsController;
use Application\DeskPRO\Entity\TmpData;
use Orb\Util\OptionsArray;

class SettingsController extends BaseSettingsController
{
    ####################################################################################################################
    # get-url-settings
    ####################################################################################################################

    public function getUrlSettingsAction()
    {
        $settings = array(
            // The custom domain being used, if any
            'cloud_custom_domain'     => $this->settings->get('core.cloud_custom_domain') ?: null,

            // The custom domain that we have configured with a custom cert
            'cloud_custom_domain_ssl' => $this->settings->get('core.cloud_custom_domain_ssl') ? true : false,

            // If the URL should be https or not
            'cloud_url_ssl'           => $this->settings->get('core.cloud_url_ssl') ? true : false,
        );

        $settings['domain_choice'] = 'default';
        if ($settings['cloud_custom_domain']) {
            $settings['domain_choice'] = 'custom';
        }

        return $this->createApiResponse(array('settings' => $settings));
    }

    ####################################################################################################################
    # save-url-settings
    ####################################################################################################################

    public function saveUrlSettingsAction()
    {
        $in_settings = new OptionsArray($this->in->getArrayValue('settings'));
        $set_settings = array();

        if ($in_settings->get('domain_choice') == 'custom') {
            $domain = preg_replace('#^https?://#', '', strtolower($in_settings->get('cloud_custom_domain')));
            $domain = trim($domain, '/');

            $url_test = 'http://' . $domain . '/';
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
                $url = 'https://' . $domain . '/';
            } else {
                $url = 'http://' . $domain . '/';
            }
            $set_settings['core.deskpro_url'] = $url;

            if ($domain != $this->settings->get('core.cloud_custom_domain')) {
                $tmpdata = new TmpData();
                $tmpdata->setType('dpc_set_domain');
                $tmpdata->setData('by_person', $this->person->getId());
                $tmpdata->setData('set_domain', $domain);
                $tmpdata->date_expire = new \DateTime('+30 minutes');

                $this->em->persist($tmpdata);
                $this->em->flush();

                $url = DP_MA_SERVER . '/cloud/call/'.DPC_SITE_ID.'/'. $tmpdata->getCode();

                try {
                    $client = new \Zend\Http\Client(null, array('timeout' => 15, 'sslverifypeer' => false));
                    $client->setMethod(\Zend\Http\Request::METHOD_GET);
                    $client->setUri($url);
                    $client->send();
                } catch (\Exception $e) {
                    return $this->createApiErrorResponse('error_activating_domain', 'There was a problem activating your custom domain. Please try again later.');
                }
            }

        } else {
            $set_settings['cloud_custom_domain'] = null;

            $set_settings['core.cloud_url_ssl'] = (bool)$in_settings->get('cloud_url_ssl');
            if ($set_settings['core.cloud_url_ssl']) {
                $url = 'https://' . DPC_SITE_DOMAIN . '/';
            } else {
                $url = 'http://' . DPC_SITE_DOMAIN . '/';
            }
            $set_settings['core.deskpro_url'] = $url;
        }

        foreach ($set_settings as $k => $v) {
            $this->settings->setSetting($k, $v);
        }

        return $this->createApiSuccessResponse();
    }
}
