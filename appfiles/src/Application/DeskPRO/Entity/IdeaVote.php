<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @category Entities
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\DeskPRO\Entity;

use \Application\DeskPRO\App;
use \Application\DeskPRO\Entity;

use \Orb\Util\Strings;

/**
 * Votes on ideas
 *
 * @orm:Entity
 * @orm:Table(name="idea_votes")
 */
class IdeaVote extends \Application\DeskPRO\Domain\DomainObject
{
	/**
	 * @var int
	 * @orm:Id @orm:generatedValue(strategy="IDENTITY") @orm:Column(name="id", type="integer")
	 */
	protected $id = null;

	/**
	 * @var \Application\DeskPRO\Entity\Idea
	 * @orm:ManyToOne(targetEntity="Idea", fetch="EAGER")
	 * @orm:JoinColumn(name="idea_id", referencedColumnName="id")
	 */
	protected $idea = null;

	/**
	 * @var \Application\DeskPRO\Entity\Person
	 * @orm:ManyToOne(targetEntity="Person", fetch="EAGER")
	 * @orm:JoinColumn(name="person_id", referencedColumnName="id")
	 */
	protected $person = null;

	/**
	 * @var \Application\DeskPRO\Entity\Visitor
	 * @orm:ManyToOne(targetEntity="Visitor", fetch="EAGER")
	 * @orm:JoinColumn(name="visitor_id", referencedColumnName="id")
	 */
	protected $visitor = null;

	/**
	 * @var int
	 * @orm:Column(name="num_votes", type="integer")
	 */
	protected $num_votes = 0;

	/**
	 * If these votes have been returned to the person
	 * 
	 * @var string
	 * @orm:Column(name="is_returned", type="boolean")
	 */
	protected $is_returned = false;

	/**
	 * @var \DateTime
	 * @orm:Column(name="date_created",type="datetime")
	 */
	protected $date_created;

	public function __consturct()
	{
		$this->date_created = new \DateTime();
	}
}