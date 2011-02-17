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

/**
 * Email sources that we need to send
 *
 * @orm:Entity
 * @orm:Table(name="sendmail_queue")
 */
class EmailQueue extends \Application\DeskPRO\Domain\DomainObject
{
	/**
	 * @var int
	 * @orm:Id @orm:generatedValue(strategy="IDENTITY") @orm:Column(name="id", type="integer")
	 */
	protected $id = null;

	/**
	 * @var string
	 * @orm:Column(name="subject", type="string", length=255)
	 */
	protected $subject;

	/**
	 * @var string
	 * @orm:Column(name="to", type="string", length=255)
	 */
	protected $to;

	/**
	 * @var int
	 * @orm:Column(name="attempts", type="integer")
	 */
	protected $attempts = 0;

	/**
	 * @var \DateTime
	 * @orm:Column(name="date_created",type="datetime")
	 */
	protected $date_created;

	public function __construct()
	{
		$this->date_created = new \DateTime();
	}
}
