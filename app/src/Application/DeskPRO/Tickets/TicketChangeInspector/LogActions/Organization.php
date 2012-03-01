<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @category Tickets
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\DeskPRO\Tickets\TicketChangeInspector\LogActions;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity;

class Organization implements LogActionInterface
{
	protected $old_org;
	protected $new_org;

	public function __construct($old_org, $new_org)
	{
		$this->old_org = $old_org;
		$this->new_org = $new_org;
	}

	public function getLogName()
	{
		return 'changed_organization';
	}

	public function getLogDetails()
	{
		return array(
			'id_before' => $this->old_org['id'] ?: null,
			'id_after'  => $this->new_org['id'] ?: null,

			'old_org_id'    => $this->old_org ? $this->old_org['id'] : 0,
			'old_org_name'  => $this->old_org ? $this->old_org['name'] : '',
			'new_org_id'    => $this->new_org ? $this->new_org['id'] : 0,
			'new_org_name'  => $this->new_org ? $this->new_org['name'] : '',
		);
	}

	public function getEventType()
	{
		return 'property';
	}
}
