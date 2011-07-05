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
 * Labels on tickets
 *
 * @orm:Entity
 * @orm:HasLifecycleCallbacks
 * @orm:Table(name="labels_news")
 */
class LabelNews extends LabelAssocAbstract
{
	const LABEL_TYPENAME = 'news';

	/**
	 * @var \Application\DeskPRO\Entity\News
	 * @orm:Id
	 * @orm:ManyToOne(targetEntity="News")
	 * @orm:JoinColumn(name="news_id", referencedColumnName="id", onDelete="cascade")
	 */
	protected $news;
}