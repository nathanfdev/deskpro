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

	/**
	 * @var \Symfony\Component\HttpFoundation\File\UploadedFile
	 */
	public $new_upload = null;
	
	public $department_id = 0;
	public $category_id   = 0;
	public $priority_id   = 0;
	public $product_id    = 0;

	public function __construct()
	{
	}
}