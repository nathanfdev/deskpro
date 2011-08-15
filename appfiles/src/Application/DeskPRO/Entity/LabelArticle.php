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
 * Labels on tickets
 *
 * @ORM_Mapping\Entity
 * @ORM_Mapping\HasLifecycleCallbacks
 * @ORM_Mapping\Table(name="labels_articles")
 */
class LabelArticle extends LabelAssocAbstract
{
	const LABEL_TYPENAME = 'articles';
	
	/**
	 * @var \Application\DeskPRO\Entity\Article
	 * @ORM_Mapping\Id
	 * @ORM_Mapping\ManyToOne(targetEntity="Article")
	 * @ORM_Mapping\JoinColumn(name="article_id", referencedColumnName="id", onDelete="cascade")
	 */
	protected $article;
}