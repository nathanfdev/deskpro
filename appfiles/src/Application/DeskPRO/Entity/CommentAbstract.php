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
	 * @var \Application\DeskPRO\Entity\Visitor
	 * @orm:ManyToOne(targetEntity="Visitor", fetch="EAGER")
	 * @orm:JoinColumn(name="visitor_id", referencedColumnName="id")
	 */
	protected $visitor = null;

	/**
	 * @var string
	 * @orm:Column(name="ip_address", type="string", length=30)
	 */
	protected $ip_address = '';

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
	protected $status = 'visible';

	/**
	 * @var \DateTime
	 * @orm:Column(name="date_created",type="datetime")
	 */
	protected $date_created;

	public function __construct()
	{
		$this->date_created = new \DateTime();
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

	public function getContentHtml()
	{
		return Markdown::format($this->content);
	}
}