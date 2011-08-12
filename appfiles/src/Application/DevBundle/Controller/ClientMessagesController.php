<?php

namespace Application\DevBundle\Controller;

use Application\DeskPRO\Build\VersionReader;
use Application\DeskPRO\Build\Upgrader;

use Application\DeskPRO\Entity\ClientMessage;

use Application\DeskPRO\App;
use Orb\Util\Arrays;
use Orb\Util\Strings;

class ClientMessagesController extends \Application\DeskPRO\HttpKernel\Controller\Controller
{
	public function indexAction()
	{
		if (!empty($_POST['client_message'])) {

			$arr = $_POST['client_message'];
			$arr = Arrays::removeFalsey($arr);
			$arr['data'] = Strings::parseEqualsLines($arr['data']);

			$cm = new ClientMessage();
			$cm->fromArray($arr);

			App::getOrm()->persist($cm);
			App::getOrm()->flush();

			$data_str = Arrays::toEqualsLines($cm['data']);
		} else {
			$cm = null;
			$data_str = '';
		}
		
		return $this->render('DevBundle:ClientMessages:index.html.php', array(
			'cm' => $cm,
			'data_str' => $data_str
		));
	}
}