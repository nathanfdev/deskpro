<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @category People
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\DeskPRO\People\ActivityLogger;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\PersonActivity;

use Application\DeskPRO\People\ActivityLogger\ActionType\ActionTypeAbstract;

use Orb\Util\Arrays;
use Orb\Util\Util;
use Orb\Util\Strings;

class ActivityLogger
{
	/**
	 * @var \Doctrine\ORM\EntityManager
	 */
	protected $em;

	public function __construct(\Doctrine\ORM\EntityManager $em)
	{
		$this->em = $em;
	}

	/**
	 * Save any action details
	 *
	 * @param \Application\DeskPRO\Entity\Person $person
	 * @param string $action_type
	 * @param array $details
	 * @return \Application\DeskPRO\Entity\PersonActivity|array
	 */
	public function saveActionDetails(Person $person, $action_type, array $details)
	{
		$activity = new PersonActivity();
		$activity->person = $person;
		$activity['action_type'] = $action_type;
		$activity['details'] = $details;

		$this->em->transactional(function($em) use ($activity) {
			$em->persist($activity);
			$em->flush();
		});

		return $activity;
	}


	/**
	 * Save an action object
	 *
	 * @param \Application\DeskPRO\Entity\Person $person
	 * @param \Application\DeskPRO\People\ActivityLogger\ActionTypeAbstract $action
	 * @return \Application\DeskPRO\Entity\PersonActivity|array
	 */
	public function saveAction(ActionTypeAbstract $action)
	{
		$action_type = Util::getBaseClassname($action);
		$action_type = Strings::camelCaseToUnderscore($action_type);

		return $this->saveActionDetails($action->getPersonContext(), $action_type, $action->getDetails());
	}
}