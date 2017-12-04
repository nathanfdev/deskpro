<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2017, DeskPRO Ltd.
 *
 * The license agreement under which this software is released
 * can be found at https://www.deskpro.com/eula/
 *
 * By using this software, you acknowledge having read the license
 * and agree to be bound thereby.
 *
 * Please note that DeskPRO is not free software. We release the full
 * source code for our software because we trust our users to pay us for
 * the huge investment in time and energy that has gone into both creating
 * this software and supporting our customers. By providing the source code
 * we preserve our customers' ability to modify, audit and learn from our
 * work. We have been developing DeskPRO since 2001, please help us make it
 * another decade.
 *
 * Like the work you see? Think you could make it better? We are always
 * looking for great developers to join us: http://www.deskpro.com/jobs/
 *
 * ~ Thanks, Everyone at Team DeskPRO
 */

/**
 * DeskPRO.
 */

namespace Application\LegacyApiBundle\Controller;

use Application\DeskPRO\Entity\Blob;
use Application\DeskPRO\Entity\BrandSetting;
use Application\DeskPRO\ResourceScanner\AdvancedSettings;
use Application\DeskPRO\Settings\GeneralPortalSettings;
use Application\DeskPRO\Settings\GeneralSettings;
use Application\DeskPRO\Settings\LoginRateLimitSettings;
use Application\DeskPRO\Settings\PasswordSettings;
use Application\DeskPRO\Settings\RegistrationSettings;
use Application\DeskPRO\Settings\ServerSettings;
use Application\DeskPRO\Settings\TicketFwdSettings;
use Application\DeskPRO\Settings\TicketSettings;
use Application\DeskPRO\CustomFields\Form\Type\PersonStartType;
use Application\LegacyApiBundle\PermissionStrategy\AdminManagePermission;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use DpSys\License;
use Orb\Util\Env;
use Orb\Util\Strings;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @ApiModes("all")
 */
class SettingsController extends AbstractController implements ProtectedControllerInterface
{
    /**
     * {@inheritdoc}
     */
    public function getPermissionStrategy()
    {
        return new AdminManagePermission();
    }

    //###################################################################################################################
    // get-value
    //###################################################################################################################

    public function getValueAction($name)
    {
        $value = $this->settings->get($name);

        return $this->createApiResponse([
            'name'  => $name,
            'value' => $value,
        ]);
    }

    //###################################################################################################################
    // set-value
    //###################################################################################################################

    public function setValueAction($name)
    {
        $value = $this->settings->setSetting($name, $this->in->getString('value'));

        return $this->createSuccessResponse([
            'name'  => $name,
            'value' => $value,
        ]);
    }

    //###################################################################################################################
    // ticket-settings
    //###################################################################################################################

    public function ticketSettingsAction()
    {
        $ticket_settings = new TicketSettings($this->settings);

        return $this->createApiResponse([
            'ticket_settings' => $ticket_settings->toArray(),
        ]);
    }

    //###################################################################################################################
    // save-ticket-settings
    //###################################################################################################################

    public function saveTicketSettingsAction()
    {
        $ticket_settings = new TicketSettings($this->settings);
        $ticket_settings->setArray($this->in->getArrayValue('ticket_settings'));
        $ticket_settings->saveSettings();

        return $this->createSuccessResponse();
    }

    //###################################################################################################################
    // ticket-settings
    //###################################################################################################################

    public function ticketFwdSettingsAction()
    {
        $ticket_fwd_settings = new TicketFwdSettings($this->settings, $this->container->getEmailAccountManager());

        return $this->createApiResponse([
            'ticket_fwd_settings' => $ticket_fwd_settings->toArray(),
        ]);
    }

    //###################################################################################################################
    // save-ticket-settings
    //###################################################################################################################

    public function saveTicketFwdSettingsAction()
    {
        $ticket_fwd_settings = new TicketFwdSettings($this->settings, $this->container->getEmailAccountManager());
        $ticket_fwd_settings->setArray($this->in->getArrayValue('ticket_fwd_settings'));
        $ticket_fwd_settings->saveSettings();

        return $this->createSuccessResponse();
    }

    //###################################################################################################################
    // server-settings
    //###################################################################################################################

    public function serverSettingsAction()
    {
        $serverSettings = new ServerSettings($this->settings);

        return $this->createApiResponse([
            'server_settings' => $serverSettings->toArray(),
        ]);
    }

    //###################################################################################################################
    // save-server-settings
    //###################################################################################################################

    public function saveServerSettingsAction()
    {
        $serverSettings = new ServerSettings($this->settings);
        $serverSettings->setArray($this->in->getArrayValue('server_settings'));
        $serverSettings->saveSettings();

        return $this->createSuccessResponse();
    }

    //###################################################################################################################
    // general-settings
    //###################################################################################################################

    public function generalSettingsAction()
    {
        /** @var \Application\DeskPRO\EntityRepository\BrandSetting $brandSettingsRepository */
        $brandSettingsRepository = $this->get('doctrine.orm.default_entity_manager')->getRepository(BrandSetting::class);
        $settings                = new GeneralSettings($this->get('brand_aware_settings_resolver'),
            $brandSettingsRepository, $this->settings);

        return $this->createApiResponse([
            'general_settings' => $settings->toArray(),
            'max_filesize'     => Env::getEffectiveMaxUploadSize(),
        ]);
    }

    //###################################################################################################################
    // save-general-settings
    //###################################################################################################################

    public function saveGeneralSettingsAction()
    {
        try {
            /** @var \Application\DeskPRO\EntityRepository\BrandSetting $brandSettingsRepository */
            $brandSettingsRepository = $this->get('doctrine.orm.default_entity_manager')->getRepository(BrandSetting::class);
            $settings                = new GeneralSettings(
                $this->get('brand_aware_settings_resolver'),
                $brandSettingsRepository,
                $this->settings
            );
            $settings->setArray($this->in->getArrayValue('general_settings'));
            $settings->saveSettings();

            return $this->createSuccessResponse();
        } catch (\Exception $e) {
            return $this->createApiErrorResponse('settings_not_saved', 'Settings were not saved.');
        }
    }

    //###################################################################################################################
    // portal-settings
    //###################################################################################################################

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @deprecated
     */
    public function portalSettingsAction()
    {
        $portal_settings = new GeneralPortalSettings($this->settings);

        return $this->createApiResponse([
            'portal_settings' => $portal_settings->toArray(),
        ]);
    }

    //###################################################################################################################
    // save-portal-settings
    //###################################################################################################################

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @deprecated
     */
    public function savePortalSettingsAction()
    {
        try {
            $settings = new GeneralPortalSettings($this->settings);
            $settings->setArray($this->in->getArrayValue('portal_settings'));
            $settings->saveSettings();

            return $this->createSuccessResponse();
        } catch (\Exception $e) {
            return $this->createApiErrorResponse('settings_not_saved', 'Settings were not saved.');
        }
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
            if (!$ext || !in_array($ext, ['gif', 'png', 'jpg', 'jpeg', 'ico'])) {
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
                    $width  = imagesx($gd);
                    $height = imagesy($gd);

                    $gd_dest = imagecreatetruecolor(16, 16);
                    imagecopyresampled($gd_dest, $gd, 0, 0, 0, 0, 16, 16, $width, $height);

                    $file_content = \phpthumb_ico::GD2ICOstring([$gd_dest]);
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
            $url     = trim($blob->getDownloadUrl(true, false), '/');

            $this->settings->setSetting('core.favicon_blob_id', $blob_id);
            $this->settings->setSetting('core.favicon_blob_url', $url);
        } else {
            $this->settings->setSetting('core.favicon_blob_id', null);
            $this->settings->setSetting('core.favicon_blob_url', null);
        }

        return $this->createSuccessResponse();
    }

    //###################################################################################################################
    // all-settings-raw
    //###################################################################################################################

    public function allSettingsRawAction()
    {
        $settings_files = new AdvancedSettings();
        $all_settings   = [];

        foreach ($settings_files->getAllSettings() as $name => $default_value) {
            $value = $set = $this->container->getSetting($name);

            if ($default_value === true) {
                $default_value = 1;
            }
            if ($value === true) {
                $value = 1;
            }
            if ($default_value === false) {
                $default_value = 0;
            }
            if ($value === false) {
                $value = 0;
            }
            if ($default_value === null) {
                $default_value = '';
            }
            if ($value === null) {
                $value = '';
            }

            if ($value === '' && $default_value != '') {
                $value = '<BLANK>';
            }

            $all_settings[$name] = [
                'name'          => $name,
                'default_value' => $default_value,
                'value'         => $value,
            ];
        }

        foreach ($this->container->getSettingsHandler()->getIterator() as $name => $value) {
            if (isset($all_settings[$name])) {
                continue;
            }

            if ($value === true) {
                $value = 1;
            }
            if ($value === false) {
                $value = 0;
            }
            if ($value === null) {
                $value = '';
            }

            $all_settings[$name] = [
                'name'          => $name,
                'default_value' => '',
                'value'         => $value,
            ];
        }

        return $this->createApiResponse([
            'all_settings' => array_values($all_settings),
        ]);
    }

    //###################################################################################################################
    // save-all-settings-raw
    //###################################################################################################################

    public function saveAllSettingsRawAction()
    {
        $settings_files = new AdvancedSettings();
        $set_settings   = $this->in->getArrayValue('all_settings');

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

    //###################################################################################################################
    // registration-settings
    //###################################################################################################################

    public function registrationSettingsAction()
    {
        $reg_settings        = new RegistrationSettings($this->settings, $this->em);
        $rate_limit_settings = new LoginRateLimitSettings($this->settings, $this->in->getString('rate_limit_context'));

        return $this->createApiResponse([
            'registration_settings' => $reg_settings->toArray(),
            'rate_limit_settings'   => $rate_limit_settings->toArray(),
        ]);
    }

    //###################################################################################################################
    // save-registration-settings
    //###################################################################################################################

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

    //###################################################################################################################
    // password-settings
    //###################################################################################################################

    public function passwordSettingsAction()
    {
        $password_settings   = new PasswordSettings($this->settings);
        $rate_limit_settings = new LoginRateLimitSettings($this->settings, $this->in->getString('rate_limit_context'));

        return $this->createApiResponse([
            'settings'            => $password_settings->toArray(),
            'rate_limit_settings' => $rate_limit_settings->toArray(),
        ]);
    }

    //###################################################################################################################
    // save-password-settings
    //###################################################################################################################

    public function savePasswordSettingsAction()
    {
        $password_settings = new PasswordSettings($this->settings);
        $password_settings->setArray($this->in->getArrayValue('settings'));
        $password_settings->saveSettings();

        $rate_limit_settings = new LoginRateLimitSettings($this->settings, $this->in->getString('rate_limit_context'));
        $rate_limit_settings->setArray($this->in->getArrayValue('rate_limit_settings'));
        $rate_limit_settings->saveSettings();

        return $this->passwordSettingsAction();
    }

    //###########################################################################
    // save-start-settings
    //###########################################################################

    public function setStartSettingsAction(Request $request)
    {
        $this->settings->setSetting('core.deskpro_url', $this->in->getString('deskpro_url'));
        $this->settings->setSetting('core.deskpro_name', $this->in->getString('deskpro_name'));

        $brand = $this->get('brand_stack')->getDefaultBrand();
        $db    = $this->get('database_connection');
        $db->delete('settings_brand', ['name' => 'core.deskpro_url', 'brand_id' => $brand->getId()]);
        $db->insert('settings_brand', [
            'name'     => 'core.deskpro_url',
            'value'    => $this->in->getString('deskpro_url'),
            'brand_id' => $brand->getId(),
        ]);

        $content = json_decode($request->getContent(), 1);

        $remove_label = false;
        if ($content && isset($content['email']) && $this->person) {
            $form = $this->createForm(new PersonStartType());
            $data = array_intersect_key($content, $form->all());
            $form->submit($data);

            if (!$form->isValid()) {
                return $this->createApiErrorResponse('form_error', (string) $form->getErrors(1));
            }

            $data            = $form->getData();
            $p               = $this->person;
            $email           = $p->getPrimaryEmail();
            $email['email']  = $data['email'];
            $p['first_name'] = $data['first_name'];
            $p['last_name']  = $data['last_name'];
            $p->setPassword($data['password']);

            $this->em->flush($email);
            $this->em->flush($p);
            $remove_label = true;
        }

        try {
            $tz = $this->in->getString('timezone');
            new \DateTimeZone($tz);
        } catch (\Exception $e) {
            $tz = 'UTC';
        }
        $this->settings->setSetting('core.default_timezone', $tz);

        $license_code = $this->in->getString('license_code');
        $lic          = License::create($license_code, $this->settings->get('core.install_key'));
        if ($lic->isLicenseCodeError()) {
            return $this->createApiErrorResponse($lic->getLicenseCodeError(), 'Invalid license (bad code)');
        }

        $this->settings->setSetting('core.license', $license_code);
        if ($remove_label) {
            $p->getLabelManager()->removeLabel('not_user');
            $this->em->flush($p);
        }

        return $this->createApiSuccessResponse();
    }

    //###########################################################################
    // set-done-initial
    //###########################################################################

    public function setDoneInitialAction()
    {
        $this->settings->setSetting('core.setup_initial', 1);

        // Attempt to clear error log from anything that might've happened during install (eg bad database etc)
        $server_error_logs = $this->container->getSystemService('server_error_logs');
        $server_error_logs->clearAllErrors();

        return $this->createApiSuccessResponse();
    }

    //###################################################################################################################
    // portal-app-settings
    //###################################################################################################################

    /**
     * @param $app
     *
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @deprecated please use Api v2
     */
    public function portalAppSettingsAction($app)
    {
        switch ($app) {
            case 'news':
                $settings = [
                    'enabled'       => (bool) $this->settings->get('core.apps_news'),
                    'tab_enabled'   => (bool) $this->settings->get('user.portal_tab_news'),
                    'subscriptions' => (bool) $this->settings->get('user.news_subscriptions'),
                ];
                break;

            case 'kb':
                $settings = [
                    'enabled'       => (bool) $this->settings->get('core.apps_kb'),
                    'tab_enabled'   => (bool) $this->settings->get('user.portal_tab_articles'),
                    'subscriptions' => (bool) $this->settings->get('user.kb_subscriptions'),
                ];
                break;

            case 'feedback':
                $settings = [
                    'enabled'       => (bool) $this->settings->get('core.apps_feedback'),
                    'tab_enabled'   => (bool) $this->settings->get('user.portal_tab_feedback'),
                    'subscriptions' => (bool) $this->settings->get('user.feedback_subscriptions'),
                ];
                break;

            case 'downloads':
                $settings = [
                    'enabled'       => (bool) $this->settings->get('core.apps_downloads'),
                    'tab_enabled'   => (bool) $this->settings->get('user.portal_tab_downloads'),
                    'subscriptions' => (bool) $this->settings->get('user.downloads_subscriptions'),
                ];
                break;

            case 'guides':
                $settings = [
                    'enabled'       => (bool) $this->settings->get('core.apps_guides'),
                    'tab_enabled'   => (bool) $this->settings->get('user.portal_tab_guides'),
                    'subscriptions' => (bool) $this->settings->get('user.guides_subscriptions'),
                ];
                break;

            default:
                throw $this->createNotFoundException();
        }

        return $this->createApiResponse([
            'settings' => $settings,
        ]);
    }

    //###################################################################################################################
    // save-portal-app-settings
    //###################################################################################################################

    /**
     * @param $app
     *
     * @throws \Exception
     *
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @deprecated use Api v2 instead
     */
    public function savePortalAppSettingsAction($app)
    {
        switch ($app) {
            case 'news':
                $settings = [
                    'core.apps_news'          => $this->in->getBoolInt('settings.enabled'),
                    'user.portal_tab_news'    => (int) ($this->in->getBool('settings.enabled') && $this->in->getBool('settings.tab_enabled')),
                    'user.news_subscriptions' => (int) $this->in->getBool('settings.subscriptions'),
                ];
                break;

            case 'kb':
                $settings = [
                    'core.apps_kb'             => $this->in->getBoolInt('settings.enabled'),
                    'user.portal_tab_articles' => (int) ($this->in->getBoolInt('settings.enabled') && $this->in->getBoolInt('settings.tab_enabled')),
                    'user.kb_subscriptions'    => (int) $this->in->getBool('settings.subscriptions'),
                ];
                break;

            case 'feedback':
                $settings = [
                    'core.apps_feedback'          => $this->in->getBoolInt('settings.enabled'),
                    'user.portal_tab_feedback'    => (int) ($this->in->getBool('settings.enabled') && $this->in->getBool('settings.tab_enabled')),
                    'user.feedback_subscriptions' => (int) $this->in->getBool('settings.subscriptions'),
                ];
                break;

            case 'downloads':
                $settings = [
                    'core.apps_downloads'          => $this->in->getBoolInt('settings.enabled'),
                    'user.portal_tab_downloads'    => (int) ($this->in->getBool('settings.enabled') && $this->in->getBool('settings.tab_enabled')),
                    'user.downloads_subscriptions' => (int) $this->in->getBool('settings.subscriptions'),
                ];
                break;

            case 'guides':
                $settings = [
                    'core.apps_guides'          => $this->in->getBoolInt('settings.enabled'),
                    'user.portal_tab_guides'    => (int) ($this->in->getBool('settings.enabled') && $this->in->getBool('settings.tab_enabled')),
                    'user.guides_subscriptions' => (int) $this->in->getBool('settings.subscriptions'),
                ];
                break;

            default:
                throw $this->createNotFoundException();
        }

        foreach ($settings as $k => $v) {
            $this->settings->setSetting($k, $v);
        }

        return $this->createApiSuccessResponse();
    }

    /**
     * @param Request $request
     *
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\TransactionRequiredException
     * @throws \Exception
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function setLogoBlobAction(Request $request)
    {
        if (!$blob_id = $request->get('blob_id')) {
            throw new NotFoundHttpException();
        }

        /** @var $blob Blob */
        if (!$blob = $this->em->find('DeskPRO:Blob', $blob_id)) {
            throw new NotFoundHttpException();
        }

        if ($old = $this->settings->get('agent.login_logo_blob_id')) {
            if ($old = $this->em->find('DeskPRO:Blob', $old)) {
                $this->container->getBlobStorage()->deleteBlobRecord($old);
            }
        }

        $blob->is_temp = false;
        $this->em->flush($blob);
        $this->settings->setSetting('agent.login_logo_blob_id', $blob_id);

        return $this->getLogoBlobAction();
    }

    /**
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\TransactionRequiredException
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function getLogoBlobAction()
    {
        /* @var Blob $blob */
        $blobId = $this->settings->get('agent.login_logo_blob_id');
        $blob   = $blobId ? $this->em->find(Blob::class, $blobId) : null;

        if ($blob) {
            $result = array_merge($blob->toApiData(), [
                'thumbnail' => rtrim($this->settings->get('core.deskpro_url'), '/').$blob->getThumbnailUrl('360x100'),
            ]);
        } else {
            $result = ['empty' => true];
        }

        return $this->createJsonResponse($result);
    }
}
