<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @category Entities
 * @copyright Copyright (c) 2011 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Ricardo Rauch <ricardo@gravityonmars.com>
 */

namespace Application\DeskPRO\Entity;

/**
 * Labels on task
 *
 * @orm:Entity
 * @orm:HasLifecycleCallbacks
 * @orm:Table(name="labels_tasks")
 */
class LabelTask extends \Application\DeskPRO\Domain\DomainObject
{

	/**
	 * @var string
	 * @orm:Id
	 * @orm:Column(name="label", type="string", length=255)
	 */
	protected $label;

	/**
	 * @var Application\DeskPRO\Entity\Task
	 * @orm:Id
	 * @orm:ManyToOne(
	 * 	targetEntity="Task",
	 * 	inversedBy="labels",
	 * 	cascade={"persist", "remove", "merge"}
	 * )
	 * @orm:JoinColumn(name="task_id", referencedColumnName="id")
	 */
	protected $task;

}

