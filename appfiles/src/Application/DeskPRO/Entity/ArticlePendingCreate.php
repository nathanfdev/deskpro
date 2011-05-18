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
use \Application\DeskPRO\Markdown;

use \Orb\Util\Strings;

/**
 * Articles that need to be created
 *
 * @orm:Entity
 * @orm:Table(name="article_pending_create")
 */
class ArticlePendingCreate extends \Application\DeskPRO\Domain\DomainObject
{
	/**
	 * @var int
	 * @orm:Id @orm:generatedValue(strategy="IDENTITY") @orm:Column(name="id", type="integer")
	 */
	protected $id = null;

	/**
	 * @var \Application\DeskPRO\Entity\Person
	 * @orm:ManyToOne(targetEntity="Person", fetch="EAGER")
	 * @orm:JoinColumn(name="person_id", referencedColumnName="id")
	 */
	protected $person = null;

	/**
	 * @var \Application\DeskPRO\Entity\Ticket
	 * @orm:ManyToOne(targetEntity="Ticket", fetch="EAGER")
	 * @orm:JoinColumn(name="ticket_id", referencedColumnName="id")
	 */
	protected $ticket = null;

	/**
	 * @var string
	 * @orm:Column(name="comment", type="string", length=1000)
	 */
	protected $comment;

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