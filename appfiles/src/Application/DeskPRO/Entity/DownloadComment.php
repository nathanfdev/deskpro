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
use Orb\Util\Strings;
use Orb\Util\Arrays;

/**
 * Comments on articles
 *
 * @orm:Entity(repositoryClass="Application\DeskPRO\EntityRepository\DownloadComment")
 * @orm:Table(name="download_comments")
 */
class DownloadComment extends CommentAbstract
{
	/**
	 * @orm:ManyToOne(targetEntity="Download", inversedBy="comment")
	 * @orm:JoinColumn(name="download_id", referencedColumnName="id", onDelete="cascade")
	 */
	protected $download;

	public function getObject()
	{
		return $this->download;
	}
}