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
 * @subpackage Import
 */

namespace Application\DeskPRO\Import\Importer\Step\Deskpro3;

class TicketLayoutStep extends AbstractDeskpro3Step
{
	/**
	 * @var \Application\DeskPRO\Import\Importer\Deskpro3Importer
	 */
	protected $importer;

	public static function getTitle()
	{
		return 'Initiate Ticket Layout';
	}

	public function run($page = 1)
	{
		$display = array();
		$display[] = array(
			'id' => 'ticket_department',
			'field_type' => 'ticket_department',
		);
		$display[] = array(
			'id' => 'ticket_priority',
			'field_type' => 'ticket_priority',
		);
		$display[] = array(
			'id' => 'ticket_priority',
			'field_type' => 'ticket_priority',
		);

		$custom_fields = $this->getDb()->fetchAll("SELECT id FROM custom_def_ticket WHERE parent_id IS NULL ORDER BY display_order ASC");
		foreach ($custom_fields as $f) {
			$id = $f['id'];
			$display[] = array(
				'id'         => "ticket_field[$id]",
				'field_type' => 'ticket_field',
				'field_id'   => $id
			);
		}

		$display[] = array(
			'id' => 'ticket_subject',
			'field_type' => 'ticket_subject',
		);
		$display[] = array(
			'id' => 'message',
			'field_type' => 'message',
		);
		$display[] = array(
			'id' => 'attachments',
			'field_type' => 'attachments',
		);

		$this->getDb()->beginTransaction();
		try {
			$this->getDb()->insert('ticket_page_display', array(
				'department_id'  => null,
				'zone'           => 'create',
				'options'        => 'a:0:{}',
				'section'        => 'default',
				'data'           => serialize($display)
			));

			$this->getDb()->commit();
		} catch (\Exception $e) {
			$this->getDb()->rollback();
			throw $e;
		}
	}
}
