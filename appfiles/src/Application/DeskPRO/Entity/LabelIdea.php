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
 * @orm:Table(name="labels_articles")
 */
class LabelIdea extends LabelAssocAbstract
{
	const LABEL_TYPENAME = 'ideas';

	/**
	 * @var \Application\DeskPRO\Entity\Idea
	 * @orm:Id
	 * @orm:ManyToOne(targetEntity="Idae")
	 * @orm:JoinColumn(name="idea_id", referencedColumnName="id")
	 */
	protected $idea;
}