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
 * News revisions
 *
 * @ORM_Mapping\Entity(repositoryClass="Application\DeskPRO\EntityRepository\NewsRevision")
 * @ORM_Mapping\Table(name="news_revisions")
 */
class NewsRevision extends RevisionAbstract
{
	/**
	 * @ORM_Mapping\ManyToOne(targetEntity="News")
	 * @ORM_Mapping\JoinColumn(name="news_id", referencedColumnName="id", onDelete="cascade")
	 */
	protected $news;

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
