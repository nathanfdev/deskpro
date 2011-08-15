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
 * Labels on downloads
 *
 * @ORM_Mapping\Entity
 * @ORM_Mapping\HasLifecycleCallbacks
 * @ORM_Mapping\Table(name="labels_downloads")
 */
class LabelDownload extends LabelAssocAbstract
{
	const LABEL_TYPENAME = 'downloads';

	/**
	 * @var \Application\DeskPRO\Entity\Article
	 * @ORM_Mapping\Id
	 * @ORM_Mapping\ManyToOne(targetEntity="Download")
	 * @ORM_Mapping\JoinColumn(name="download_id", referencedColumnName="id", onDelete="cascade")
	 */
	protected $download;
}