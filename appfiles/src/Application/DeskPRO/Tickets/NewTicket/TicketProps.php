<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage UserBundle
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\DeskPRO\Tickets\NewTicket;

use Application\DeskPRO\App;

/**
 * This wraps up the 'ticket' data of a newticket
 */
class TicketProps
{
	public $subject = '';
	public $message = '';
	
	public $department_id = 0;
	public $category_id   = 0;
	public $priority_id   = 0;
	public $product_id    = 0;

	public $custom_fields = array();

	public function __construct()
	{
		// Need to null out field_x values or else we'll get
		// notices when symfony tries to bind them in the Form
		// TODO should clean this up, but not work it now when
		// Symfony lists say the From component will change quite significantly soon

		for ($i = 0; $i < 100; $i++) {
			$this->custom_fields["field_$i"] = null;
		}
	}
}