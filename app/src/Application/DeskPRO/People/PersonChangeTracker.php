<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. http://www.deskpro.com/   |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2012, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at http://www.deskpro.com/license                           |
|                                                                          |
| By using this software, you acknowledge having read the license          |
| and agree to be bound thereby.                                           |
|                                                                          |
| Please note that DeskPRO is not free software. We release the full       |
| source code for our software because we trust our users to pay us for    |
| the huge investment in time and energy that has gone into both creating  |
| this software and supporting our customers. By providing the source code |
| we preserve our customers' ability to modify, audit and learn from our   |
| work. We have been developing DeskPRO since 2001, please help us make it |
| another decade.                                                          |
|                                                                          |
| Like the work you see? Think you could make it better? We are always     |
| looking for great developers to join us: http://www.deskpro.com/jobs/    |
|                                                                          |
| ~ Thanks, Everyone at Team DeskPRO                                       |
\**************************************************************************/

/**
 * DeskPRO
 *
 * @package DeskPRO
 * @category Tickets
 */

namespace Application\DeskPRO\People;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity\Person;

use Orb\Util\Arrays;

/**
 * The personlistener listens for changes to a ticket, and then runs inspections once the changes
 * are committed.
 */
class PersonChangeTracker extends \Application\DeskPRO\Domain\ChangeTracker
{
	/**
	 * @var \Application\DeskPRO\Entity\Person
	 */
	protected $person;

	/**
	 * @var bool
	 */
	protected $is_new_person = false;

	/**
	 * @var bool
	 */
	protected $running = false;

	public function __construct(Person $person)
	{
		$this->entity = $person;
		$this->person = $person;

		if (!$person['id']) {
			$this->is_new_person = true;
		}
	}


	/**
	 * Get the person
	 *
	 * @return \Application\DeskPRO\Entity\Person
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
		if ($this->running) {
			return;
		}
		$this->running = true;

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
			$orgem = App::getContainer()->getEm()->createQuery("
				SELECT od
				FROM DeskPRO:OrganizationEmailDomain od
				WHERE od.domain = ?1
			")->setParameter(1, $domain)->setMaxResults(1)->getOneOrNullResult();

			if ($orgem) {
				$change = true;
				$this->person->organization = $orgem->organization;
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

		$this->running = false;
	}
}
