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
 * Attachments are binary file data that can be attached to various things.
 *
 * @Entity
 * @HasLifecycleCallbacks
 * @Table(name="attachments_blobs")
 * )
 */
class AttachmentBlob extends \DeskPRO\Domain\DomainObject
{
	/**
	 * @var int
	 * @Id @Column(name="id", type="integer")
	 * @GeneratedValue
	 */
	protected $id = null;

	/**
	 * @var int
	 * @Column(name="attachment_id", type="integer")
	 */
	protected $attachment_id;

	/**
	 * @var \Application\CoreBundle\Entity\Attachment
	 * @ManyToOne(targetEntity="Attachment")
	 * @JoinColumn(name="attachment_id", referencedColumnName="id")
	 */
	protected $attachment;

	/**
	 * The users name (best guess from other sources etc)
	 *
	 * @TODO This needs to be a binary type
	 *
	 * @var string
	 * @Column(name="name", type="text")
	 */
	protected $data;
}