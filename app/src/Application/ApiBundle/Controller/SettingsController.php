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
 */

namespace Application\ApiBundle\Controller;

use Application\ApiBundle\PermissionStrategy\AdminManagePermission;
use Application\DeskPRO\CacheInvalidator\UserPageCache;
use Application\DeskPRO\ResourceScanner\AdvancedSettings;
use Application\DeskPRO\Settings\GeneralSettings;
use Application\DeskPRO\Settings\LoginRateLimitSettings;
use Application\DeskPRO\Settings\PasswordSettings;
use Application\DeskPRO\Settings\PortalSettings;
use Application\DeskPRO\Settings\RegistrationSettings;
use Application\DeskPRO\Settings\ServerSettings;
use Application\DeskPRO\Settings\TicketFwdSettings;
use Application\DeskPRO\Settings\TicketSettings;
use DeskPRO\Kernel\License;
use Orb\Util\Env;
use Orb\Util\Strings;

class SettingsController extends AbstractController implements ProtectedControllerInterface
{
    /**
     * {@inheritDoc}
     */
    public function getPermissionStrategy()
    {
        return new AdminManagePermission();
    }


    ####################################################################################################################
    # get-value
    ####################################################################################################################

    public function getValueAction($name)
    {
        $value = $this->settings->get($name);

        return $this->createApiResponse(array(
            'name'  => $name,
            'value' => $value,
        ));
    }


    ####################################################################################################################
    # set-value
    ####################################################################################################################

    public function setValueAction($name)
    {
        $value = $this->settings->setSetting($name, $this->in->getString('value'));

        return $this->createSuccessResponse(array(
            'name'  => $name,
            'value' => $value
        ));
    }


    ####################################################################################################################
    # ticket-settings
    ####################################################################################################################

    public function ticketSettingsAction()
    {
        $ticket_settings = new TicketSettings($this->settings);

        return $this->createApiResponse(array(
            'ticket_settings' => $ticket_settings->toArray(),
        ));
    }


    ####################################################################################################################
    # save-ticket-settings
    ####################################################################################################################

    public function saveTicketSettingsAction()
    {
        $ticket_settings = new TicketSettings($this->settings);
        $ticket_settings->setArray($this->in->getArrayValue('ticket_settings'));
        $ticket_settings->saveSettings();

        return $this->createSuccessResponse();
    }

    ####################################################################################################################
    # ticket-settings
    ####################################################################################################################

    public function ticketFwdSettingsAction()
    {
        $ticket_fwd_settings = new TicketFwdSettings($this->settings, $this->container->getEmailAccountManager());

        return $this->createApiResponse(array(
            'ticket_fwd_settings' => $ticket_fwd_settings->toArray(),
        ));
    }


    ####################################################################################################################
    # save-ticket-settings
    ####################################################################################################################

    public function saveTicketFwdSettingsAction()
    {
        $ticket_fwd_settings = new TicketFwdSettings($this->settings, $this->container->getEmailAccountManager());
        $ticket_fwd_settings->setArray($this->in->getArrayValue('ticket_fwd_settings'));
        $ticket_fwd_settings->saveSettings();

        return $this->createSuccessResponse();
    }

    ####################################################################################################################
    # server-settings
    ####################################################################################################################

    public function serverSettingsAction()
    {
        $server_settings = new ServerSettings($this->settings);

        return $this->createApiResponse(array(
            'server_settings' => $server_settings->toArray(),
        ));
    }


    ####################################################################################################################
    # save-server-settings
    ####################################################################################################################

    public function saveServerSettingsAction()
    {
        $server_settings = new ServerSettings($this->settings);
        $server_settings->setArray($this->in->getArrayValue('server_settings'));
        $server_settings->saveSettings();

        return $this->createSuccessResponse();
    }

    ####################################################################################################################
    # general-settings
    ####################################################################################################################

    public function generalSettingsAction()
    {
        $general_settings = new GeneralSettings($this->settings);

        return $this->createApiResponse(array(
            'general_settings' => $general_settings->toArray(),
            'max_filesize'     => Env::getEffectiveMaxUploadSize(),
        ));
    }


    ####################################################################################################################
    # save-general-settings
    ####################################################################################################################

    public function saveGeneralSettingsAction()
    {
        $general_settings = new GeneralSettings($this->settings);
        $general_settings->setArray($this->in->getArrayValue('general_settings'));
        $general_settings->saveSettings();

        return $this->createSuccessResponse();
    }


    ####################################################################################################################
    # portal-settings
    ####################################################################################################################

    public function portalSettingsAction()
    {
        $portal_settings = new PortalSettings($this->settings);

        return $this->createApiResponse(array(
            'portal_settings' => $portal_settings->toArray(),
        ));
    }


    ####################################################################################################################
    # save-portal-settings
    ####################################################################################################################

    public function savePortalSettingsAction()
    {
        $portal_settings = new PortalSettings($this->settings);
        $portal_settings->setArray($this->in->getArrayValue('portal_settings'));
        $portal_settings->saveSettings();

        return $this->createSuccessResponse();
    }

    public function saveCustomFaviconAction($blob_id, $blob_auth)
    {
        $blob = null;
        if ($blob_id) {
            $blob = $this->em->find('DeskPRO:Blob', $blob_id);
            if (!$blob || $blob->authcode != $blob_auth) {
                throw $this->createNotFoundException();
            }
        }

        if ($blob) {

            $ext = strtolower(Strings::getExtension($blob->getFilename()));
            if (!$ext || !in_array($ext, array('gif', 'png', 'jpg', 'jpeg', 'ico'))) {
                throw $this->createNotFoundException();
            }

            $file = $this->container->getBlobStorage()->copyBlobRecordToString($blob);

            if ($blob->content_type != 'image/x-icon' && $blob->content_type != 'image/vnd.microsoft.icon') {
                if (class_exists('Imagick')) {
                    $im = new \Imagick();
                    try {
                        $im->readimageblob($file, $blob->getFilename());
                    } catch (\Exception $e) {
                        throw $this->createNotFoundException();
                    }
                    $im->scaleImage(16, 16, true);
                    $im->setImageFormat('ico');
                    $file_content = $im->getImageBlob();
                } else {
                    $gd = @imagecreatefromstring($file);
                    if (!$gd) {
                        throw $this->createNotFoundException();
                    }
                    $width = imagesx($gd);
                    $height = imagesy($gd);

                    $gd_dest = imagecreatetruecolor(16, 16);
                    imagecopyresampled($gd_dest, $gd, 0, 0, 0, 0, 16, 16, $width, $height);

                    $file_content = \phpthumb_ico::GD2ICOstring(array($gd_dest));
                }
            } else {
                $file_content = $file;
            }

            $use_blob = $this->container->getBlobStorage()->createBlobRecordFromString(
                $file_content,
                'favicon.ico',
                'image/x-icon'
            );
            $blob_id = $use_blob->getId();
            $url = trim($blob->getDownloadUrl(true, false), '/');

            $this->settings->setSetting('core.favicon_blob_id', $blob_id);
            $this->settings->setSetting('core.favicon_blob_url', $url);

        } else {
            $this->settings->setSetting('core.favicon_blob_id', null);
            $this->settings->setSetting('core.favicon_blob_url', null);
        }

        $cache = new UserPageCache();
        $cache->invalidateAll();

        return $this->createSuccessResponse();
    }


    ####################################################################################################################
    # all-settings-raw
    ####################################################################################################################

    public function allSettingsRawAction()
    {
        $settings_files = new AdvancedSettings();
        $all_settings = array();

        foreach ($settings_files->getAllSettings() as $name => $default_value) {
            $value = $set = $this->container->getSetting($name);

            if ($default_value === true) $default_value = 1;
            if ($value === true) $value = 1;
            if ($default_value === false) $default_value = 0;
            if ($value === false) $value = 0;
            if ($default_value === null) $default_value = '';
            if ($value === null) $value = '';

            if ($value === '' && $default_value != '') {
                $value = '<BLANK>';
            }

            $all_settings[$name] = array(
                'name'          => $name,
                'default_value' => $default_value,
                'value'         => $value,
            );
        }

        foreach ($this->container->getSettingsHandler()->getIterator() as $name => $value) {
            if (isset($all_settings[$name])) continue;

            if ($value === true) $value = 1;
            if ($value === false) $value = 0;
            if ($value === null) $value = '';

            $all_settings[$name] = array(
                'name'          => $name,
                'default_value' => '',
                'value'         => $value
            );
        }

        return $this->createApiResponse(array(
            'all_settings' => array_values($all_settings),
        ));
    }

    ####################################################################################################################
    # save-all-settings-raw
    ####################################################################################################################

    public function saveAllSettingsRawAction()
    {
        $settings_files = new AdvancedSettings();
        $set_settings = $this->in->getArrayValue('all_settings');

        foreach ($settings_files->getAllSettings() as $name => $default_value) {
            if (!isset($set_settings[$name])) {
                continue;
            }

            $value = trim($set_settings[$name]);
            if ($value === '' || $value == $default_value) {
                $this->settings->setSetting($name, null);
            } elseif ($value == '<BLANK>') {
                $this->settings->setSetting($name, '');
            } else {
                $this->settings->setSetting($name, $value);
            }
        }

        return $this->createSuccessResponse();
    }


    ####################################################################################################################
    # registration-settings
    ####################################################################################################################

    public function registrationSettingsAction()
    {
        $reg_settings = new RegistrationSettings($this->settings, $this->em);
        $rate_limit_settings = new LoginRateLimitSettings($this->settings, $this->in->getString('rate_limit_context'));

        return $this->createApiResponse(array(
            'registration_settings' => $reg_settings->toArray(),
            'rate_limit_settings' => $rate_limit_settings->toArray(),
        ));
    }

    ####################################################################################################################
    # save-registration-settings
    ####################################################################################################################

    public function saveRegistrationSettingsAction()
    {
        $reg_settings = new RegistrationSettings($this->settings, $this->em);
        $reg_settings->setArray($this->in->getArrayValue('registration_settings'));
        $reg_settings->saveSettings();

        $rate_limit_settings = new LoginRateLimitSettings($this->settings, $this->in->getString('rate_limit_context'));
        $rate_limit_settings->setArray($this->in->getArrayValue('rate_limit_settings'));
        $rate_limit_settings->saveSettings();

        return $this->createSuccessResponse();
    }

    ####################################################################################################################
    # password-settings
    ####################################################################################################################

    public function passwordSettingsAction()
    {
        $password_settings = new PasswordSettings($this->settings);
        $rate_limit_settings = new LoginRateLimitSettings($this->settings, $this->in->getString('rate_limit_context'));

        return $this->createApiResponse(array(
            'settings' => $password_settings->toArray(),
            'rate_limit_settings' => $rate_limit_settings->toArray(),
        ));
    }

    ####################################################################################################################
    # save-password-settings
    ####################################################################################################################

    public function savePasswordSettingsAction()
    {
        $password_settings = new PasswordSettings($this->settings);
        $password_settings->setArray($this->in->getArrayValue('settings'));
        $password_settings->saveSettings();

        $rate_limit_settings = new LoginRateLimitSettings($this->settings, $this->in->getString('rate_limit_context'));
        $rate_limit_settings->setArray($this->in->getArrayValue('rate_limit_settings'));
        $rate_limit_settings->saveSettings();

        return $this->createSuccessResponse();
    }

    ############################################################################
    # save-start-settings
    ############################################################################

    public function setStartSettingsAction()
    {
        $this->settings->setSetting('core.deskpro_url', $this->in->getString('deskpro_url'));
        $this->settings->setSetting('core.deskpro_name', $this->in->getString('deskpro_name'));

        try {
            $tz = $this->in->getString('timezone');
            new \DateTimeZone($tz);
        } catch (\Exception $e) {
            $tz = 'UTC';
        }
        $this->settings->setSetting('core.default_timezone', $tz);

        $license_code = $this->in->getString('license_code');
        $lic = License::create($license_code, $this->settings->get('core.install_key'));
        if ($lic->isLicenseCodeError()) {
            return $this->createApiErrorResponse($lic->getLicenseCodeError(), 'Invalid license (bad code)');
        }

        $this->settings->setSetting('core.license', $license_code);

        return $this->createApiSuccessResponse();
    }

    ############################################################################
    # set-done-initial
    ############################################################################

    public function setDoneInitialAction()
    {
        $this->settings->setSetting('core.setup_initial', 1);

        // Attempt to clear error log from anything that might've happened during install (eg bad database etc)
        $server_error_logs = $this->container->getSystemService('server_error_logs');
        $server_error_logs->clearAllErrors();

        return $this->createApiSuccessResponse();
    }

    ####################################################################################################################
    # portal-app-settings
    ####################################################################################################################

    public function portalAppSettingsAction($app)
    {
        switch ($app) {
            case 'news':
                $settings = array(
                    'enabled'     => (bool)$this->settings->get('core.apps_news'),
                    'tab_enabled' => (bool)$this->settings->get('user.portal_tab_news'),
                );
                break;

            case 'kb':
                $settings = array(
                    'enabled'     => (bool)$this->settings->get('core.apps_kb'),
                    'tab_enabled' => (bool)$this->settings->get('user.portal_tab_articles'),
                );
                break;

            case 'feedback':
                $settings = array(
                    'enabled'     => (bool)$this->settings->get('core.apps_feedback'),
                    'tab_enabled' => (bool)$this->settings->get('user.portal_tab_feedback'),
                );
                break;

            case 'downloads':
                $settings = array(
                    'enabled'     => (bool)$this->settings->get('core.apps_downloads'),
                    'tab_enabled' => (bool)$this->settings->get('user.portal_tab_downloads'),
                );
                break;

            default:
                throw $this->createNotFoundException();
        }

        return $this->createApiResponse(array(
            'settings' => $settings
        ));
    }

    ####################################################################################################################
    # save-portal-app-settings
    ####################################################################################################################

    public function savePortalAppSettingsAction($app)
    {
        switch ($app) {
            case 'news':
                $settings = array(
                    'core.apps_news'       => $this->in->getBoolInt('settings.enabled'),
                    'user.portal_tab_news' => (int)($this->in->getBool('settings.enabled') && $this->in->getBool('settings.tab_enabled'))
                );
                break;

            case 'kb':
                $settings = array(
                    'core.apps_kb'             => $this->in->getBoolInt('settings.enabled'),
                    'user.portal_tab_articles' => (int)($this->in->getBoolInt('settings.enabled') && $this->in->getBoolInt('settings.tab_enabled'))
                );
                break;

            case 'feedback':
                $settings = array(
                    'core.apps_feedback'       => $this->in->getBoolInt('settings.enabled'),
                    'user.portal_tab_feedback' => (int)($this->in->getBool('settings.enabled') && $this->in->getBool('settings.tab_enabled'))
                );
                break;

            case 'downloads':
                $settings = array(
                    'core.apps_downloads'       => $this->in->getBoolInt('settings.enabled'),
                    'user.portal_tab_downloads' => (int)($this->in->getBool('settings.enabled') && $this->in->getBool('settings.tab_enabled'))
                );
                break;

            default:
                throw $this->createNotFoundException();
        }

        foreach ($settings as $k => $v) {
            $this->settings->setSetting($k, $v);
        }

        return $this->createApiSuccessResponse();
    }
}
