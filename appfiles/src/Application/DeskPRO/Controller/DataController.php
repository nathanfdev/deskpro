<?php

namespace Application\DeskPRO\Controller;

use \Application\DeskPRO\App;
use \Application\DeskPRO\Entity;

use \Orb\Util\Util;
use \Orb\Util\Arrays;

class DataController extends AbstractController
{
	public function interfaceDataAction()
	{
		$what = $this->in->getCleanValueArray('types', 'string', 'discard');
		
		$js = array();

		
		$js = implode("\n", $js);
		$response = App::getResponse();
		$response->headers->set('Content-Type', 'application/javascript');
		$response->setContent($js);

		return $response;
	}
}