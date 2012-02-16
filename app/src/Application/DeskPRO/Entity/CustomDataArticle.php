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
 * Custom article data
 *
 * @ORM_Mapping\Entity
 * @ORM_Mapping\HasLifecycleCallbacks
 * @ORM_Mapping\Table(name="custom_data_article", indexes={
 *     @ORM_Mapping\Index(name="obj_id_idx", columns={"article_id"}),
 *     @ORM_Mapping\Index(name="field_id_idx", columns={"field_id","article_id"})
 * })
 */
class CustomDataArticle extends CustomDataAbstract
{
	/**
	 * @var \Application\DeskPRO\Entity\Article
	 * @ORM_Mapping\ManyToOne(targetEntity="Article")
	 * @ORM_Mapping\JoinColumn(name="article_id", referencedColumnName="id", onDelete="cascade")
	 */
	protected $article;

	/**
	 * @var \Application\DeskPRO\Entity\CustomDefArticle
	 * @ORM_Mapping\ManyToOne(targetEntity="CustomDefArticle", fetch="EAGER")
	 * @ORM_Mapping\JoinColumn(name="field_id", referencedColumnName="id", onDelete="cascade")
	 */
	protected $field = null;

	public function getArticleId()
	{
		return $this->article['id'];
	}
}
