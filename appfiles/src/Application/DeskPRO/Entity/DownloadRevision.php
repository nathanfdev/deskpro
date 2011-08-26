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
 * Download revisions
 *
 * @ORM_Mapping\Entity(repositoryClass="Application\DeskPRO\EntityRepository\DownloadRevision")
 * @ORM_Mapping\Table(name="download_revisions")
 */
class DownloadRevision extends RevisionAbstract
{
	/**
	 * @ORM_Mapping\ManyToOne(targetEntity="Download")
	 * @ORM_Mapping\JoinColumn(name="download_id", referencedColumnName="id", onDelete="cascade")
	 */
	protected $download;

	/**
	 * @var \Application\DeskPRO\Entity\Blob
	 * @ORM_Mapping\ManyToOne(targetEntity="Blob", fetch="EAGER")
	 * @ORM_Mapping\JoinColumn(name="blob_id", referencedColumnName="id")
	 */
	protected $blob = null;

	/**
	 * @var string
	 * @ORM_Mapping\Column(name="title", type="string")
	 */
	protected $title = '';

	/**
	 * @var string
	 * @ORM_Mapping\Column(name="content", type="text")
	 */
	protected $content = '';
}
