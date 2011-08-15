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

use Doctrine\ORM\Mapping as ORM_Mapping;

/**
 * Records labels on people.
 *
 * @ORM_Mapping\Entity
 * @ORM_Mapping\Table(name="labels_tasks")
 */
class LabelTask extends \Application\DeskPRO\Domain\DomainObject
{
  /**
   * @var string
   * @ORM_Mapping\Id
   * @ORM_Mapping\Column(name="label", type="string", length=255)
   */
  protected $label;

  /**
   * @var \Application\DeskPRO\Entity\Task
   * @ORM_Mapping\Id
   * @ORM_Mapping\ManyToOne(targetEntity="Task")
   * @ORM_Mapping\JoinColumn(name="task_id", referencedColumnName="id", onDelete="cascade")
   */
  protected $task;
  
}
