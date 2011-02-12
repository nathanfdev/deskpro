<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage AgentBundle
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\AgentBundle\Controller;

use \Orb\Util\Strings;
use \Orb\Util\Arrays;

use Application\DeskPRO\App;

/**
 * Handles AJAX serving of client messages
 */
class PollerController extends AbstractController
{
	public function getNewMessagesAction($since)
	{
		$since = new \DateTime("@$since");

		$data = array();
		$all_messages = App::getEntityRepository('DeskPRO:ClientMessage')->getMessagesForPrivateId("person:{$this->person['id']}", $since);
		foreach ($all_messages as $message) {
			$handler = $message->getHandler();

			$data[] = $handler->getMessage('ajax');
		}

		return $this->createJsonResponse($data);
	}
}