<?php

namespace Application\AdminBundle\Controller\Helper;

use \Application\DeskPRO\App;
use \Application\DeskPRO\Util as DeskPRO_Util;

use Orb\Util\Arrays;

class DisplayOrderUpdate
{
	protected $controller;

	public function __construct($controller)
	{
		$this->controller = $controller;
	}

	public function doUpdate($table)
	{
		$ordered_ids = $this->controller->in->getCleanValueArray('display_order', 'uint', 'discard');
		$ordered_ids = Arrays::removeFalsey($ordered_ids);

		DeskPRO_Util::updateDisplayOrders($ordered_ids, $table);

		return $this->controller->createJsonResponse(array('success' => true, 'new_order' => $ordered_ids));
	}
}