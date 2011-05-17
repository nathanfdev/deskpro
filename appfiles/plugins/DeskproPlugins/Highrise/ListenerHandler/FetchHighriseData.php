<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage Plugins
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace DeskproPlugins\Highrise\ListenerHandler;

use Application\DeskPRO\App;

class FetchHighriseData
{
	protected $highrise;

	public function __construct()
	{
		$this->highrise = new  \Orb\Service\Highrise\Highrise(
			App::getSetting('dp_highrise.highrise_url'),
			App::getSetting('dp_highrise.api_auth_key')
		);
	}

	public function DeskPRO_onDisplayFieldRenderHtml($event)
	{
		$ticket = $event->ticket;
		$person = $ticket->person;

		$person_resource = \Orb\Service\Highrise\Resource\Person($this->highrise);
		$notes_resource = \Orb\Service\Highrise\Resource\Notes($this->highrise);
		$results = $person_resource->findPeopleWithCriteria(array('email' => $person->getPrimaryEmailAddress()));
		if (!$results OR empty($results[0])) {
			return '';
		}

		$result_person = $results[0];
		$result_notes = $notes_resource->getNotesForPerson($result_person['id']);

		$tpl = App::getTemplating();

		$event->html = $tpl->render('dp_highrise::highrise_results.html.twig', array(
			'highrise_person' => $result_person,
			'highrise_notes' => $result_notes
		));
	}
}