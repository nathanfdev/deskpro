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

class Category implements LogActionInterface
{
	protected $old_cat;
	protected $new_cat;

	public function __construct($old_cat, $new_cat)
	{
		$this->old_cat = $old_cat;
		$this->new_cat = $new_cat;
	}

	public function getLogName()
	{
		return 'changed_category';
	}

	public function getLogDetails()
	{
		return array(
			'old_category_id' => $this->old_cat['id'],
			'old_category_title' => $this->old_cat['title'],
			'new_category_id' => $this->new_cat['id'],
			'new_category_title' => $this->new_cat['title'],
		);
	}

	public function getEventType()
	{
		return 'property';
	}
}
