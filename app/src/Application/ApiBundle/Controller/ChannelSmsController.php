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
use Application\ApiBundle\PermissionStrategy\MultiPermissions;
use Application\ApiBundle\PermissionStrategy\PassPermission;
use Application\DeskPRO\Entity\PhoneNumber;
use Application\DeskPRO\Entity\SmsAccount;
use Orb\Sms\Provider\TwilioSmsProvider;

class ChannelSmsController extends AbstractController implements ProtectedControllerInterface
{
	/**
	 * {@inheritDoc}
	 */
	public function getPermissionStrategy()
	{
		$multi = new MultiPermissions();
		$multi->addPermissionStrategy(new AdminManagePermission());
		$multi->addPermissionStrategy(new PassPermission(), 'listAction');

		return $multi;
	}


	####################################################################################################################
	# list sms accounts
	####################################################################################################################

	public function listAction()
	{
		$accounts = $this->getSmsAccountRepo()->findAll();

		$data = $this->getContainer()->getSerializer()->serializeArray($accounts);

		return $this->createApiResponse(array('sms_accounts' => $data));
	}


	####################################################################################################################
	# get sms account
	####################################################################################################################

	public function getAction($id)
	{
		$account = $this->getSmsAccountRepo()->find($id);

		if (!$account) {
			return $this->createApiErrorResponse('not_found', sprintf('sms account (id=%s) does not exist', $id));
		}

		$data = $this->getContainer()->getSerializer()->serialize($account);

		return $this->createApiResponse($data);
	}


	####################################################################################################################
	# save sms account
	####################################################################################################################

	public function saveAction($id = null)
	{
		if ($id) {
			$account = $this->getContainer()->getEm()->getRepository('DeskPRO:SmsAccount')->find($id);

			if (!$account) {
				return $this->createApiErrorResponse('sms.account_not_found', 'sms account not found', 404);
			}
		} else {
			$account = new SmsAccount();
		}


		$account->type                 = $this->in->getValue('type');
		$account->params               = $this->in->getValue('params');
		$account->identifier           = $this->in->getValue('identifier');
		$account->phone_number         = new PhoneNumber();
		$account->phone_number->number = $this->in->getValue('phone_number');
		$account->is_enabled           = $this->in->getValue('is_enabled');
		$account->is_tested            = $this->in->getValue('is_tested');
		$account->is_connected         = $this->in->getValue('is_connected');

		$this->getContainer()->getEm()->persist($account);
		$this->getContainer()->getEm()->flush();

		return $this->createApiSuccessResponse(array());
	}


	####################################################################################################################
	# connect to a provider and return provider specific info
	####################################################################################################################


	/**
	 * Client expects json response with {
	 * success: true,
	 * numbers: [{display_name: 'friendly number name', phone_number: '+19023330302'}, ...etc],
	 * friendly_name: 'name' a friendly name to call this account (usually a provider account name, their email, etc)
	 * }
*@return Response
	 */
	public function connectProviderAction()
	{
		$type = $this->in->getValue('type');

		switch ($type) {
			case 'twilio':
				$sid = $this->in->getValue('params.sid');
				$auth_token = $this->in->getValue('params.auth_token');
				$provider = new TwilioSmsProvider($sid, $auth_token);

				try {
					$data = $provider->getIncomingNumbers();
					$name = $provider->getAccountName();

					return $this->createApiSuccessResponse(array('numbers' => $data, 'friendly_name' => $name));
				} catch (\Exception $e) {
					return $this->createApiErrorResponse('sms.connection_error', 'Could not connect. Please check your credentials');
				}
		}

		$res = $this->createApiErrorResponse('sms.connection_error', 'Invalid SMS account type');

		return $res;
	}


	/**
	 * @return \Doctrine\ORM\EntityRepository
	 */
	private function getSmsAccountRepo()
	{
		return $this->getContainer()->getEm()->getRepository('DeskPRO:SmsAccount');
	}
}
