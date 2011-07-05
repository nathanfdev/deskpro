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
 * Raw email data
 *
 * @orm:Entity
 * @orm:Table(name="email_sources_blobs")
 */
class EmailSourceBlob extends \Application\DeskPRO\Domain\DomainObject
{
	/**
	 * @var int
	 * @orm:Id @orm:generatedValue(strategy="IDENTITY") @orm:Column(name="id", type="integer")
	 * @GeneratedValue
	 */
	protected $id = null;

	/**
	 * @var \Application\DeskPRO\Entity\EmailSource
	 * @orm:ManyToOne(targetEntity="EmailSource")
	 * @orm:JoinColumn(name="source_id", referencedColumnName="id", onDelete="cascade")
	 */
	protected $source = null;

	/**
	 * @TODO This needs to be a binary type
	 *
	 * @var string
	 * @orm:Column(name="data", type="text")
	 */
	protected $data;
}