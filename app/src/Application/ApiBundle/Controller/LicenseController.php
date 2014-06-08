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

use Application\ApiBundle\PermissionStrategy\AdminManagePermission;
use Application\DeskPRO\Entity\TmpData;
use Application\DeskPRO\Service\LicenseService;
use DeskPRO\Kernel\License;
use Orb\Util\Dates;
use Orb\Validator\StringEmail;


class LicenseController extends AbstractController implements ProtectedControllerInterface
{
	/**
	 * {@inheritDoc}
	 */
	public function getPermissionStrategy()
	{
		return new AdminManagePermission();
	}


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
				'expireDate'  => $lic->getExpireDate() ? $lic->getExpireDate()->format($this->settings->get('core.date_full')) : null,
				'isExpired'   => $is_expired,
				'expireDays'  => $expire_in_days,
				'isDemo'      => $lic->isDemo() ? true : false,
				'maxAgents'   => $lic->getMaxAgents(),
				'licenseCode' => $lic->getLicenseCode(),
			),
			'lic_set_callback' => License::getLicServer() . '/api/license/set-license.json',
			'ma_token'         => $ma_token->toApiData(),
			'ma_login_url'     => $ma_login_url,
		));
	}

	####################################################################################################################
	# set-license
	####################################################################################################################

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

		return $this->createApiResponse(array(
			'success' => true
		));
	}

	####################################################################################################################
	# download-keyfile
	####################################################################################################################

	public function downloadKeyfileAction($_format = 'txt')
	{
		$email_address = $this->in->getString('email_address');
		if (!$email_address) {
			$email_address = $this->person->getPrimaryEmailAddress();
		}

		$install_data = array();
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
			return $this->createJsonResponse(array(
				'filename'     => 'deskpro-keyfile.txt',
				'filesize'     => strlen($file),
				'content_type' => 'plain/text',
				'data'         => $file
			));
		}
	}


	####################################################################################################################
	# send-support-request
	####################################################################################################################

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


	####################################################################################################################
	# get-version-info
	####################################################################################################################

	public function getVersionInfoAction()
	{
		return $this->createJsonResponse(array(
			'build'          => DP_BUILD_TIME,
			'build_name'     => defined('DP_BUILD_NUM') && DP_BUILD_NUM ? DP_BUILD_NUM : 'DEV',
			'build_num_base' => defined('DP_BUILD_NUM_BASE') ? DP_BUILD_NUM_BASE : 0,
			'build_num_rev'  => defined('DP_BUILD_NUM_REV') ? DP_BUILD_NUM_REV : 0
		));
	}

	####################################################################################################################
	# get-latest-version
	####################################################################################################################

	public function getLatestVersionAction()
	{
        global $DP_CONFIG;

		if (isset($DP_CONFIG['debug']['disable_version_check'])) {
			$version_info = null;
		} else {
			try {
				$version_info = LicenseService::compareVersion();
			} catch (\Exception $e) {
				$version_info = null;
			}
		}

		return $this->createJsonResponse(array(
			'version_info' => $version_info,
		));
	}

	####################################################################################################################
	# get-news
	####################################################################################################################

	public function getNewsAction()
	{
		global $DP_CONFIG;

		if (isset($DP_CONFIG['debug']['disable_version_check'])) {
			$news = null;
		} else {
			try {
				$news = LicenseService::getNews();
			} catch (\Exception $e) {
				$news = null;
			}
		}

		return $this->createJsonResponse(array(
			'news' => $news,
		));
	}
}
