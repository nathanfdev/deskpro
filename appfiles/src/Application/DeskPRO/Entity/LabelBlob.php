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

use Doctrine\ORM\Mapping as ORM_Mapping;

/**
 * Labels on blobs
 *
 * @ORM_Mapping\Entity
 * @ORM_Mapping\HasLifecycleCallbacks
 * @ORM_Mapping\Table(name="labels_blobs")
 */
class LabelBlob extends LabelAssocAbstract
{
	const LABEL_TYPENAME = 'blobs';
	
	/**
	 * @var \Application\DeskPRO\Entity\Blob
	 * @ORM_Mapping\Id
	 * @ORM_Mapping\ManyToOne(targetEntity="Blob")
	 * @ORM_Mapping\JoinColumn(name="blob_id", referencedColumnName="id", onDelete="cascade")
	 */
	protected $blob;
}