<?php

/**
 * DeskPRO.
 */

namespace Application\LegacyApiBundle\Controller;

use Application\DeskPRO\Entity\TmpData;
use Application\DeskPRO\Service\LicenseService;
use Application\LegacyApiBundle\PermissionStrategy\AdminManagePermission;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use DpSys\License;
use Orb\Util\Dates;
use Orb\Validator\StringEmail;

/**
 * @ApiModes("all")
 */
class LicenseController extends AbstractController implements ProtectedControllerInterface
{
    /**
     * {@inheritdoc}
     */
    public function getPermissionStrategy()
    {
        return new AdminManagePermission();
    }

    //###################################################################################################################
    // get-license
    //###################################################################################################################

    public function getLicenseAction()
    {
        $lic = License::getLicense();

        $is_expired     = false;
        $expire_in_days = 0;

        if ($lic->getExpireDate()) {
            $is_expired = $lic->getExpireDate()->format('U') < time();
            if (!$is_expired) {
                $lic_expire_parts = Dates::secsToPartsArray($lic->getExpireDate()->format('U') - time());
                $expire_in_days   = $lic_expire_parts['days'];
                $expire_in_days += $lic_expire_parts['years'] * 365;
            }
        }

        $lic_info = [
            'licenseId'   => $lic->getLicenseId(),
            'org'         => $lic->get('org') ?: null,
            'expireDate'  => $lic->getExpireDate() ? $lic->getExpireDate()->format($this->settings->get('core.date_full')) : null,
            'isExpired'   => $is_expired,
            'expireDays'  => $expire_in_days,
            'isDemo'      => $lic->isDemo() ? true : false,
            'maxAgents'   => $lic->getMaxAgents(),
            'licenseCode' => $lic->getLicenseCode(),
        ];

        $active_agents = $this->container->getDb()->fetchColumn('
            SELECT COUNT(*)
            FROM people
            WHERE is_agent = 1 AND is_deleted = 0
        ');
        $limits = [
            'max_agents'    => $lic->getMaxAgents() ?: -1,
            'count_agents'  => $active_agents,
            'remain_agents' => $lic->getMaxAgents() ? max(0, $lic->getMaxAgents() - $active_agents) : -1,
        ];

        $ma_token = TmpData::create(
            'ma_login', [
                'email_address' => $this->person->getPrimaryEmailAddress(),
            ], '+1 hour'
        );
        $this->em->persist($ma_token);
        $this->em->flush();

        $ma_login_url = License::getSecureLicServer().'/login_check_license';
        if (strpos($ma_login_url, 'www.deskpro.com') && strpos($ma_login_url, 'https://') === 0) {
            $ma_login_url = str_replace('http://', 'https://', $ma_login_url);
        }

        if ($custom_code = $this->settings->get('custom_cloud_billing_authcode')) {
            $code                 = 'XX-'.$custom_code;
            $custom_billing_frame = DP_MA_SERVER_SECURE.'/cloud/start/'.$this->settings->get('custom_cloud_billing_siteid').'/'.$code;
            if (defined('DP_CLOUD_LIC_URL')) {
                $custom_billing_frame = str_replace(
                    ['{SITE_ID}', '{SITE_AUTH}'],
                    [$this->settings->get('custom_cloud_billing_siteid'), $code],
                    DP_CLOUD_LIC_URL
                );
            }
        } else {
            $custom_billing_frame = null;
        }

        return $this->createApiResponse([
            'license'              => $lic_info,
            'limits'               => $limits,
            'lic_set_callback'     => License::getSecureLicServer().'/api/license/set-license.json',
            'ma_token'             => $ma_token->toApiData(),
            'ma_login_url'         => $ma_login_url,
            'custom_billing_frame' => $custom_billing_frame,
        ]);
    }

    //###################################################################################################################
    // set-license
    //###################################################################################################################

    public function setLicenseAction()
    {
        $license_code = $this->in->getString('license_code');

        $lic = License::create($license_code, $this->settings->get('core.install_key'));
        if ($lic->isLicenseCodeError()) {
            return $this->createApiErrorResponse($lic->getLicenseCodeError(), 'Invalid license (bad code)');
        }

        $this->em->getConnection()->beginTransaction();
        try {
            $this->settings->setSetting('core.license', $license_code);
            $this->em->getConnection()->commit();
        } catch (\Exception $e) {
            $this->em->getConnection()->rollback();
            throw $e;
        }

        return $this->createApiResponse([
            'success' => true,
        ]);
    }

    //###################################################################################################################
    // download-keyfile
    //###################################################################################################################

    public function downloadKeyfileAction($_format = 'txt')
    {
        $email_address = $this->in->getString('email_address');
        if (!$email_address) {
            $email_address = $this->person->getPrimaryEmailAddress();
        }

        $install_data                          = [];
        $install_data['install_key']           = $this->settings->get('core.install_key');
        $install_data['install_token']         = $this->settings->get('core.install_token');
        $install_data['request_email_address'] = $this->person->getPrimaryEmailAddress();
        $install_data['email_address']         = $email_address;
        $install_data['url']                   = $this->request->getUriForPath('/');

        $install_data = json_encode($install_data);
        $install_data = base64_encode($install_data);

        $file = <<<FILE
Email this file to support@deskpro.com and our agents will generate a license code for you
==============================DP_INSTALLKEY_BGN==============================
$install_data
==============================DP_INSTALLKEY_END==============================
FILE;

        $file = trim($file);
        $file .= "\n";

        if ($_format == 'txt') {
            $res = $this->createResponse($file);
            $res->headers->set('Content-Disposition', 'attachment; filename=deskpro-keyfile.txt');
            $res->headers->set('Content-Type', 'plain/text; filename=deskpro-keyfile.txt');

            return $res;
        } else {
            return $this->createJsonResponse([
                'filename'     => 'deskpro-keyfile.txt',
                'filesize'     => strlen($file),
                'content_type' => 'plain/text',
                'data'         => $file,
            ]);
        }
    }

    //###################################################################################################################
    // send-support-request
    //###################################################################################################################

    public function sendSupportRequestAction()
    {
        $email = $this->in->getString('contact.email');
        if (!$email || !StringEmail::isValueValid($email)) {
            $email = $this->person->getPrimaryEmailAddress();
        }

        $ret = \Application\DeskPRO\Service\ErrorReporter::sendSupportMessage(
            $this->in->getString('contact.subject'),
            $this->in->getString('contact.message'),
            $this->person->getDisplayName(),
            $email
        );

        if ($ret) {
            return $this->createSuccessResponse();
        } else {
            return $this->createApiErrorResponse('failed_send', 'Failed to send support request.');
        }
    }

    //###################################################################################################################
    // get-version-info
    //###################################################################################################################

    public function getVersionInfoAction()
    {
        $appEnv = $this->get('deskpro.app_env');

        return $this->createJsonResponse([
            'build_id'   => $appEnv->getBuildId(),
            'build_name' => $appEnv->getVersionName(),
        ]);
    }

    //###################################################################################################################
    // get-latest-version
    //###################################################################################################################

    public function getLatestVersionAction()
    {
        try {
            $instanceReader = $this->getContainer()->get('dp.updater.instance_reader');
            $distroLoader   = $this->getContainer()->get('dp.updater.distro.manifest_loader');
            $releases       = $distroLoader->loadReleases();

            $instanceStatus = $instanceReader->getInstanceStatus($releases);

            $versionInfo = [
                'count_behind' => $instanceStatus->isOutdated() ? max(1, $instanceStatus->getNumBetween()) : 0,
                'days_old'     => $instanceStatus->isOutdated() ? max(1, $instanceStatus->getDaysOld()) : 0,
                'build_id'     => $instanceStatus->getLatestRelease()->getId(),
                'build_name'   => $instanceStatus->getLatestRelease()->getName(),
            ];
        } catch (\Exception $e) {
            $versionInfo = null;
        }

        return $this->createJsonResponse([
            'version_info' => $versionInfo,
        ]);
    }

    //###################################################################################################################
    // get-news
    //###################################################################################################################

    public function getNewsAction()
    {
        try {
            $news = LicenseService::getNews();
        } catch (\Exception $e) {
            $news = null;
        }

        return $this->createJsonResponse([
            'news' => $news,
        ]);
    }
}
