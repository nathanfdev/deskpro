<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. http://www.deskpro.com/   |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2012, DeskPRO Ltd.             |
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

namespace Application\ApiBundle\Controller;

use Application\DeskPRO\Entity\TmpData;
use DeskPRO\Kernel\License;
use Orb\Util\Dates;


class LicenseController extends AbstractController
{
	###################################################################################################################
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

		$ma_token = TmpData::create('ma_login', array(
			'email_address' => $this->person->getPrimaryEmailAddress()
		), '+1 hour');
		$this->em->persist($ma_token);
		$this->em->flush($ma_token);

		$ma_login_url = License::getLicServer() . '/login_check_license';
		if (strpos($ma_login_url, 'www.deskpro.com') && strpos($ma_login_url, 'https://') === 0) {
			$ma_login_url = str_replace('http://', 'https://', $ma_login_url);
		}

		return $this->createApiResponse(array(
			'license' => array(
				'licenseId'   => $lic->getLicenseId(),
				'org'         => $lic->get('org') ?: null,
				'expireDate'  => $lic->getExpireDate()->format($this->settings->get('core.date_full')),
				'isExpired'   => $is_expired,
				'expireDays'  => $expire_in_days,
				'isDemo'      => $lic->isDemo() ? true : false,
				'maxAgents'   => $lic->getMaxAgents(),
				'licenseCode' => $lic->getLicenseCode(),
			),
			'lic_set_callback' => License::getLicServer() . '/api/license/set-license.json',
			'ma_token'         => $ma_token,
			'ma_login_url'     => $ma_login_url,
		));
	}

	###################################################################################################################
	# save-license
	####################################################################################################################

	public function saveLicenseAction()
	{
		$license_code = $this->in->getString('license_code');
		error_log($license_code);

		return $this->createApiResponse(array(
			'success' => true
		));

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

		return $this->createApiResponse(array(
			'success' => true
		));
	}
}
