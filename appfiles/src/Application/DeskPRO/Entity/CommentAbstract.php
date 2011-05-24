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

use Application\DeskPRO\Markdown;

use Orb\Util\Util;
use Orb\Util\Strings;
use Orb\Util\Arrays;

/**
 * Base comments
 *
 * @orm:MappedSuperclass
 */
class CommentAbstract extends \Application\DeskPRO\Domain\DomainObject
{
	const STATUS_VISIBLE    = 'visible';
	const STATUS_VALIDATING = 'validating';
	const STATUS_DELETED    = 'deleted';

	/**
	 * The unique ID.
	 *
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
	 * @var string
	 * @orm:Column(name="email", type="string", length=255, nullable=true)
	 */
	protected $email = null;

	/**
	 * @var string
	 * @orm:Column(name="name", type="string", length=255, nullable=true)
	 */
	protected $name = null;

	/**
	 * @var string
	 * @orm:Column(name="content", type="text")
	 */
	protected $content;

	/**
	 * @var string
	 * @orm:Column(name="status", type="string", length=30)
	 */
	protected $status;

	/**
	 * @var \DateTime
	 * @orm:Column(name="date_created",type="datetime")
	 */
	protected $date_created;

	public function __construct()
	{
		$this->status = 'visible';
		$this->date_created = new \DateTime();
	}

	public function getContentHtml()
	{
		return Markdown::format($this->content);
	}
}