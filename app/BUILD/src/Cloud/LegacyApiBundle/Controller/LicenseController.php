<?php

/**
 * DeskPRO.
 */

namespace Cloud\LegacyApiBundle\Controller;

use Application\DeskPRO\Entity\TmpData;
use Application\LegacyApiBundle\Controller\LicenseController as BaseLicenseController;
use DpSys\License;
use Orb\Util\Dates;

class LicenseController extends BaseLicenseController
{
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

        $current_agents = $this->db->fetchColumn('
            SELECT COUNT(*)
            FROM people
            WHERE is_agent = 1 AND is_deleted = 0
        ');

        $max_agents = License::getLicense()->getMaxAgents();

        return $this->createApiResponse([
            'license' => [
                'expireDate'  => $lic->getExpireDate() ? $lic->getExpireDate()->format($this->settings->get('core.date_full')) : null,
                'isExpired'   => $is_expired,
                'expireDays'  => $expire_in_days,
                'isDemo'      => $lic->isDemo() ? true : false,
                'maxAgents'   => $lic->getMaxAgents(),
                'licenseCode' => '',
            ],
            'limits' => [
                'max_agents'    => $max_agents,
                'count_agents'  => $current_agents,
                'remain_agents' => 1, // override for cloud because we handle it automatically
            ],
        ]);
    }

    //###################################################################################################################
    // get-billing-login-token
    //###################################################################################################################

    public function getBillingLoginTokenAction()
    {
        $tmpdata = new TmpData();
        $tmpdata->setType('dpc_billing_access');
        $tmpdata->setData('person_info', [
            'helpdesk_url'   => rtrim($this->container->getBrandSetting('core.deskpro_url'), '/'),
            'asset_url'      => str_replace('/index.php', '', rtrim($this->container->getBrandSetting('core.deskpro_url'), '/')),
            'person_id'      => $this->person->getId(),
            'first_name'     => $this->person->first_name,
            'last_name'      => $this->person->last_name,
            'name'           => $this->person->getDisplayName(),
            'email'          => $this->person->getPrimaryEmailAddress(),
            'picture_url_24' => $this->person->getPictureUrl(24),
            'can_admin'      => $this->person->can_admin,
            'can_agent'      => $this->person->can_agent,
            'can_billing'    => $this->person->can_billing,
            'can_reports'    => $this->person->can_reports,
            'can_portal'     => $this->container->getSetting('user.portal_enabled'),
        ]);
        $tmpdata->date_expire = new \DateTime('+15 minutes');

        $this->em->persist($tmpdata);
        $this->em->flush();

        $code = $tmpdata->getCode();

        $url = DP_MA_SERVER_SECURE.'/cloud/start/'.DPC_SITE_ID.'/'.$code;
        if (defined('DP_CLOUD_LIC_URL')) {
            $url = str_replace(
                ['{SITE_ID}', '{SITE_AUTH}'],
                [DPC_SITE_ID, $code],
                DP_CLOUD_LIC_URL
            );
        }

        return $this->createJsonResponse([
            'code'   => $code,
            'ma_url' => $url,
        ]);
    }
}
