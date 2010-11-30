<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @category Entities
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris@nadeau.ws>
 */

namespace Application\CoreBundle\Entity;

/**
 * Raw email data
 *
 * @orm:Entity
 * @orm:HasLifecycleCallbacks
 * @orm:Table(name="email_sources_blobs")
 */
class EmailSourceBlob extends \DeskPRO\Domain\DomainObject
{
	/**
	 * @var int
	 * @orm:Id @orm:Column(name="id", type="integer")
	 * @GeneratedValue
	 */
	protected $id = null;

	/**
	 * @var int
	 * @orm:Column(name="source_id", type="integer")
	 */
	protected $source_id;

	/**
	 * @var \Application\CoreBundle\Entity\EmailSource
	 * @orm:OneToOne(targetEntity="EmailSource")
	 * @orm:JoinColumn(name="source_id", referencedColumnName="id")
	 */
	protected $source = null;

	/**
	 * @TODO This needs to be a binary type
	 *
	 * @var string
	 * @orm:Column(name="name", type="text")
	 */
	protected $data;
}