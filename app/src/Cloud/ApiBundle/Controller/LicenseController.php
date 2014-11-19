<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/  |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2014, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at http://www.deskpro.com/license                           |
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

use Application\ApiBundle\Controller\LicenseController as BaseLicenseController;
use Application\DeskPRO\Entity\TmpData;
use DeskPRO\Kernel\License;
use Orb\Util\Dates;

class LicenseController extends BaseLicenseController
{
    ####################################################################################################################
    # get-license
    ####################################################################################################################

    public function getLicenseAction()
    {
        $lic = License::getLicense();

        $is_expired = false;
        $expire_in_days = 0;

        if ($lic->getExpireDate()) {
            $is_expired = $lic->getExpireDate()->format('U') < time();
            if (!$is_expired) {
                $lic_expire_parts = Dates::secsToPartsArray($lic->getExpireDate()->format('U') - time());
                $expire_in_days = $lic_expire_parts['days'];
                $expire_in_days += $lic_expire_parts['years'] * 365;
            }
        }

        $current_agents = $this->db->fetchColumn("
            SELECT COUNT(*)
            FROM people
            WHERE is_agent = 1 AND is_deleted = 0
        ");

        $max_agents = License::getLicense()->getMaxAgents();

        return $this->createApiResponse(array(
            'license' => array(
                'expireDate'  => $lic->getExpireDate() ? $lic->getExpireDate()->format($this->settings->get('core.date_full')) : null,
                'isExpired'   => $is_expired,
                'expireDays'  => $expire_in_days,
                'isDemo'      => $lic->isDemo() ? true : false,
                'maxAgents'   => $lic->getMaxAgents(),
                'licenseCode' => '',
            ),
            'limits' => array(
                'max_agents'    => $max_agents,
                'count_agents'  => $current_agents,
                'remain_agents' => 1 // override for cloud because we handle it automatically
            ),
        ));
    }

    ####################################################################################################################
    # get-billing-login-token
    ####################################################################################################################

    public function getBillingLoginTokenAction()
    {
        $tmpdata = new TmpData();
        $tmpdata->setType('dpc_billing_access');
        $tmpdata->setData('person_info', array(
            'helpdesk_url'     => rtrim($this->container->getSetting('core.deskpro_url'), '/'),
            'asset_url'        => str_replace('/index.php', '', rtrim($this->container->getSetting('core.deskpro_url'), '/')),
            'person_id'        => $this->person->getId(),
            'first_name'       => $this->person->first_name,
            'last_name'        => $this->person->last_name,
            'name'             => $this->person->getDisplayName(),
            'email'            => $this->person->getPrimaryEmailAddress(),
            'picture_url_24'   => $this->person->getPictureUrl(24),
            'can_admin'        => $this->person->can_admin,
            'can_agent'        => $this->person->can_agent,
            'can_billing'      => $this->person->can_billing,
            'can_reports'      => $this->person->can_reports,
            'can_portal'       => $this->container->getSetting('user.portal_enabled'),
        ));
        $tmpdata->date_expire = new \DateTime('+15 minutes');

        $this->em->persist($tmpdata);
        $this->em->flush();

        return $this->createJsonResponse(array(
            'code'   => $tmpdata->getCode(),
            'ma_url' => DP_MA_SERVER . '/cloud/start/'.DPC_SITE_ID.'/'. $tmpdata->getCode()
        ));
    }
}
