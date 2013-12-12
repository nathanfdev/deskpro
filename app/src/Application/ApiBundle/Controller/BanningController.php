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

use Application\DeskPRO\Exception\ValidationException;
use Application\DeskPRO\Banning\IpBanEdit;
use Application\DeskPRO\Banning\Form\Type\IpBanType;
use Application\DeskPRO\Banning\Form\Type\IpBanPropsType;

use Orb\Util\Arrays;

class BanningController extends AbstractController
{
	####################################################################################################################
	# list
	####################################################################################################################

	public function listAction()
	{
		/**
		 * @var \Application\DeskPRO\Banning\IpBans $ip_bans
		 */

		$ip_bans = $this->container->getSystemService('ip_bans');



		return $this->createApiResponse(
			array(
				 'bans' => array(
					 'ip_bans'    => $ip_bans->getAll(),
					 'email_bans' => array(),
				 )
			)
		);
	}

	###################################################################################################################
	# get IP
	####################################################################################################################

	public function getIpAction($id)
	{
		/**
		 * @var \Application\DeskPRO\Banning\IpBans $ip_bans
		 */

		$ip_bans = $this->container->getSystemService('ip_bans');
		$ip_ban  = $ip_bans->getById($id);

		if (!$ip_ban) {

			throw $this->createNotFoundException();
		}

		return $this->createApiResponse(
			array(
				 'ip_ban' => $this->getApiData($ip_ban)
			)
		);
	}

	####################################################################################################################
	# save IP
	####################################################################################################################

	public function saveIpAction($id)
	{
		/**
		 * @var \Application\DeskPRO\Banning\IpBans $ip_bans
		 */

		$ip_bans = $this->container->getSystemService('ip_bans');

		if ($id) {

			$ip_ban = $ip_bans->getById($id);

			if (!$ip_ban) {

				throw $this->createNotFoundException();
			}
		} else {

			$ip_ban = $ip_bans->createNew();
		}

		$postData = $this->in->getAll('post');

		$ip_ban_edit = new IpBanEdit($ip_ban);

		$form = $this->createForm(new IpBanType(), $ip_ban_edit, array('cascade_validation' => true));
		$form->submit($this->deleteExtraDataFromRequest($form, $postData, 'ip_ban'), true);

		if ($form->isValid()) {

			$ip_ban_edit->save($this->em);

		} else {

			throw ValidationException::create($this->getFormValidationErrorsString($form));
		}

		return $this->createApiResponse(
			array(
				 'success'   => true,
				 'banned_ip' => $ip_ban->banned_ip
			)
		);
	}
}