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
use Orb\Util\Strings;
use Orb\Util\Arrays;

/**
 * Comments on articles
 *
 * @ORM_Mapping\Entity(repositoryClass="Application\DeskPRO\EntityRepository\DownloadComment")
 * @ORM_Mapping\Table(name="download_comments")
 */
class DownloadComment extends CommentAbstract
{
	/**
	 * @ORM_Mapping\ManyToOne(targetEntity="Download", inversedBy="comment")
	 * @ORM_Mapping\JoinColumn(name="download_id", referencedColumnName="id", onDelete="cascade")
	 */
	protected $download;

	public function getObject()
	{
		return $this->download;
	}
}