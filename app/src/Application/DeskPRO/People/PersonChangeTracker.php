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

namespace Application\DeskPRO\People;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity;

use Orb\Util\Arrays;

/**
 * The personlistener listens for changes to a ticket, and then runs inspections once the changes
 * are committed.
 */
class PersonChangeTracker extends \Application\DeskPRO\Domain\ChangeTracker
{
	protected $ticket;
	protected $is_new_person = false;

	public function __construct(Entity\Person $person)
	{
		$this->entity = $person;
		$this->person = $person;

		if (!$person['id']) {
			$this->is_new_person = true;
		}
	}


	/**
	 * Get the ticket
	 */
	public function getPerson()
	{
		return $this->person;
	}



	/**
	 * Was the person new (just created?)
	 *
	 * @return bool
	 */
	public function isNewPerson()
	{
		return $this->is_new_person;
	}



	public function propertyChanged($sender, $prop, $old_val, $new_val)
	{
		if (in_array($prop, array('notes'))) {
			$this->recordMultiPropertyChanged($prop, $old_val, $new_val);
		} else {
			$this->recordPropertyChanged($prop, $old_val, $new_val);
		}
	}



	/**
	 * Notify all listeners that changes to the person have been committed
	 *
	 * @return void
	 */
	public function done()
	{
		if ($this->is_new_person && $this->person->getPrimaryEmail()) {
			$change = false;

			$rules = App::getContainer()->getEm()->getRepository('DeskPRO:UserRule')->getMatching($this->person->getEmailAddress());
			if ($rules) {
				foreach ($rules as $r) {
					if ($r->add_usergroup) {
						$change = true;
						$this->person->addUsergroup($r->add_usergroup);
					}
					if ($r->add_organization) {
						$change = true;
						$this->person->organization = $r->add_organization;
					}
				}
			}

			// And check orgs with domain assocs
			$domain = $this->person->getPrimaryEmail()->email_domain;
			$org = App::getContainer()->getEm()->createQuery("
				SELECT od
				FROM DeskPRO:OrganizationEmailDomain od
				WHERE od.domain = ?1
			")->setParameter(1, $domain)->setMaxResults(1)->getOneOrNullResult();

			if ($org) {
				$change = true;
				$this->person->organization = $org;
			}

			if ($change) {
				App::getContainer()->getEm()->getConnection()->beginTransaction();

				try {
					App::getContainer()->getEm()->persist($this->person);
					App::getContainer()->getEm()->getConnection()->commit();
				} catch (\Exception $e) {
					App::getContainer()->getEm()->getConnection()->rollback();
					throw $e;
				}
			}
		}
	}
}
