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
 * @category Entities
 */

namespace Application\DeskPRO\EntityRepository;

use Application\DeskPRO\App;
use \Doctrine\ORM\EntityRepository;
use Application\DeskPRO\Entity;

use Orb\Util\Numbers;

class Draft extends AbstractEntityRepository
{
	/**
	 * @param $content_type
	 * @param $content_id
	 * @param \Application\DeskPRO\Entity\Person $person
	 *
	 * @return \Application\DeskPRO\Entity\Draft|null
	 */
	public function getDraft($content_type, $content_id, Entity\Person $person = null)
	{
		if (!$person) {
			$person = App::getCurrentPerson();
		}

		return $this->getEntityManager()->createQuery('
			SELECT d
			FROM DeskPRO:Draft d
			WHERE d.content_type = ?0
				AND d.content_id = ?1
				AND d.person = ?2
		')->setParameters(array($content_type, $content_id, $person))->getOneOrNullResult();
	}

	/**
	 * @param string $content_type
	 * @param integer $content_id
	 * @param string $message
	 * @param array $extras
	 * @param \Application\DeskPRO\Entity\Person $person
	 *
	 * @return \Application\DeskPRO\Entity\Draft
	 */
	public function insertDraft($content_type, $content_id, $message, array $extras = array(), Entity\Person $person = null)
	{
		if (!$person) {
			$person = App::getCurrentPerson();
		}

		$draft = $this->getDraft($content_type, $content_id, $person);
		if (!$draft) {
			$draft = new \Application\DeskPRO\Entity\Draft();
		}

		$draft->date_created = new \DateTime();
		$draft->content_type = $content_type;
		$draft->content_id = $content_id;
		$draft->message = $message;
		$draft->extras = $extras;
		$draft->person = $person;

		$this->getEntityManager()->persist($draft);
		$this->getEntityManager()->flush();

		return $draft;
	}

	public function deleteDraft($content_type, $content_id, Entity\Person $person = null)
	{
		if (!$person) {
			$person = App::getCurrentPerson();
		}

		App::getDb()->delete('drafts', array(
			'content_type' => $content_type,
			'content_id' => $content_id,
			'person_id' => $person->id
		));
	}

	public function deleteDraftsForContent($content_type, $content_id)
	{
		App::getDb()->delete('drafts', array(
			'content_type' => $content_type,
			'content_id' => $content_id
		));
	}
}
