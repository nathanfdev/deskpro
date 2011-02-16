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

namespace Application\DeskPRO\Tickets\TicketLog\Actions;

use \Application\DeskPRO\App;
use \Application\DeskPRO\Entity;

class Product implements LogActionInterface
{
	protected $old_product;
	protected $new_product;

	public function __construct($old_product, $new_product)
	{
		$this->old_product = $old_product;
		$this->new_product = $new_product;
	}

	public function getLogName()
	{
		return 'changed_product';
	}

	public function getLogDetails()
	{
		return array(
			'old_product_id' => $this->old_product['id'],
			'old_product_title' => $this->old_product['title'],
			'new_product_id' => $this->new_product['id'],
			'new_product_title' => $this->new_product['title'],
		);
	}

	public function getEventType()
	{
		return 'property';
	}
}