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

use Application\DeskPRO\Departments\Form\Type\TicketDepartmentType;
use Application\DeskPRO\Departments\TicketDepartmentEdit;
use Application\DeskPRO\Departments\TicketDepartmentEditor;
use Application\DeskPRO\Entity\Department;
use Application\DeskPRO\Settings\SettingHandler\TicketDepartment as TicketDepartmentHandler;
use Application\DeskPRO\Exception\ValidationException;
use Orb\Util\Arrays;

class TicketStatusesController extends AbstractController
{
	####################################################################################################################
	# get-closed-info
	####################################################################################################################

	public function getClosedInfoAction()
	{
		$info = array(
			'enabled'           => $this->settings->get('core_tickets.use_archive'),
			'auto_archive_time' => $this->settings->get('core_tickets.auto_archive_time'),
		);

		return $this->createApiResponse(array(
			'closed_info' => $info
		));
	}

	####################################################################################################################
	# get-deleted-info
	####################################################################################################################

	public function getDeletedInfoAction()
	{
		$info = array(
			'auto_purge_time' => $this->settings->get('core_tickets.hard_delete_time'),
		);

		return $this->createApiResponse(array(
			'deleted_info' => $info
		));
	}

	####################################################################################################################
	# get-spam-info
	####################################################################################################################

	public function getSpamInfoAction()
	{
		$info = array(
			'auto_purge_time' => $this->settings->get('core_tickets.spam_delete_time'),
		);

		return $this->createApiResponse(array(
			'spam_info' => $info
		));
	}
}