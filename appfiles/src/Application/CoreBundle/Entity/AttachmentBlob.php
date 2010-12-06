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
 * @orm:Entity
 * @orm:HasLifecycleCallbacks
 * @orm:Table(name="attachments_blobs")
 * )
 */
class AttachmentBlob extends \Application\DeskPRO\Domain\DomainObject
{
	/**
	 * @var int
	 * @orm:Id @orm:generatedValue(strategy="IDENTITY") @orm:Column(name="id", type="integer")
	 * @GeneratedValue
	 */
	protected $id = null;

	/**
	 * @var int
	 * @orm:Column(name="attachment_id", type="integer")
	 */
	protected $attachment_id;

	/**
	 * @var \Application\CoreBundle\Entity\Attachment
	 * @orm:ManyToOne(targetEntity="Attachment")
	 * @orm:JoinColumn(name="attachment_id", referencedColumnName="id")
	 */
	protected $attachment;

	/**
	 * The users name (best guess from other sources etc)
	 *
	 * @TODO This needs to be a binary type
	 *
	 * @var string
	 * @orm:Column(name="name", type="text")
	 */
	protected $data;
}