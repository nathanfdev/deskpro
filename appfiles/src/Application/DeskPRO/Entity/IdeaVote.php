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

use Doctrine\ORM\Mapping as ORM_Mapping;

use \Application\DeskPRO\App;
use \Application\DeskPRO\Entity;

use Orb\Util\Strings;
use Orb\Util\Numbers;

/**
 * Votes on ideas
 *
 * @ORM_Mapping\Entity
 * @ORM_Mapping\Table(name="idea_votes")
 */
class IdeaVote extends \Application\DeskPRO\Domain\DomainObject
{
	/**
	 * @var int
	 * @ORM_Mapping\Id @ORM_Mapping\generatedValue(strategy="IDENTITY") @ORM_Mapping\Column(name="id", type="integer")
	 */
	protected $id = null;

	/**
	 * @var \Application\DeskPRO\Entity\Idea
	 * @ORM_Mapping\ManyToOne(targetEntity="Idea", fetch="EAGER")
	 * @ORM_Mapping\JoinColumn(name="idea_id", referencedColumnName="id", onDelete="cascade")
	 */
	protected $idea = null;

	/**
	 * @var \Application\DeskPRO\Entity\Person
	 * @ORM_Mapping\ManyToOne(targetEntity="Person", fetch="EAGER")
	 * @ORM_Mapping\JoinColumn(name="person_id", referencedColumnName="id", onDelete="set null")
	 */
	protected $person = null;

	/**
	 * @var \Application\DeskPRO\Entity\Visitor
	 * @ORM_Mapping\ManyToOne(targetEntity="Visitor", fetch="EAGER")
	 * @ORM_Mapping\JoinColumn(name="visitor_id", referencedColumnName="id", onDelete="set null")
	 */
	protected $visitor = null;

	/**
	 * @var string
	 * @ORM_Mapping\Column(name="ip_address", type="string", length=30)
	 */
	protected $ip_address;

	/**
	 * @var string
	 * @ORM_Mapping\Column(name="email", type="string", length=255, nullable=true)
	 */
	protected $email = null;

	/**
	 * @var string
	 * @ORM_Mapping\Column(name="name", type="string", length=255, nullable=true)
	 */
	protected $name = null;
	
	/**
	 * @var int
	 * @ORM_Mapping\Column(name="num_votes", type="integer")
	 */
	protected $num_votes = 0;

	/**
	 * If these votes have been returned to the person
	 * 
	 * @var string
	 * @ORM_Mapping\Column(name="is_returned", type="boolean")
	 */
	protected $is_returned = false;

	/**
	 * @var \DateTime
	 * @ORM_Mapping\Column(name="date_created",type="datetime")
	 */
	protected $date_created;

	public function __consturct()
	{
		$this->date_created = new \DateTime();
	}

	public function setNumVotes($votes)
	{
		$votes = Numbers::bound($votes, 1, App::getSetting('core_ideas.max_votes_ideas'));
		$this->_onPropertyChanged('num_votes', $this->num_votes, $votes);

		$this->num_votes = $votes;
	}

	public function setVisitor(Visitor $visitor = null)
	{
		$this->_onPropertyChanged('visitor', $this->visitor, $visitor);
		$this->visitor = $visitor;

		if ($visitor === null) return;

		$this['ip_address'] = $visitor['ip_address'];

		if (!$this->name AND $visitor['name']) {
			$this['name'] = $visitor['name'];
		}
		if (!$this->email AND $visitor['email']) {
			$this['email'] = $visitor['email'];
		}
	}
}