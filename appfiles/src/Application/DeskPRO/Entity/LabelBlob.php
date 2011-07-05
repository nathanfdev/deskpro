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
 * Labels on blobs
 *
 * @orm:Entity
 * @orm:HasLifecycleCallbacks
 * @orm:Table(name="labels_blobs")
 */
class LabelBlob extends LabelAssocAbstract
{
	const LABEL_TYPENAME = 'blobs';
	
	/**
	 * @var \Application\DeskPRO\Entity\Blob
	 * @orm:Id
	 * @orm:ManyToOne(targetEntity="Blob")
	 * @orm:JoinColumn(name="blob_id", referencedColumnName="id", onDelete="cascade")
	 */
	protected $blob;
}