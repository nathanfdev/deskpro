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
 * @Entity
 * @HasLifecycleCallbacks
 * @Table(name="email_sources_blobs")
 */
class EmailSourceBlob extends \DeskPRO\Domain\DomainObject
{
	/**
	 * @var int
	 * @Id @Column(name="id", type="integer")
	 * @GeneratedValue
	 */
	protected $id = null;

	/**
	 * @var int
	 * @Column(name="source_id", type="integer")
	 */
	protected $source_id;

	/**
	 * @TODO This needs to be a binary type
	 *
	 * @var string
	 * @Column(name="name", type="text")
	 */
	protected $data;
}