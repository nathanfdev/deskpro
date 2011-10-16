<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @category Entities
 * @copyright Copyright (c) 2011 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Abdullah Kiser <kiser.bd@gmail.com>
 */

namespace Application\DeskPRO\Entity;

use Doctrine\ORM\Mapping as ORM_Mapping;

/**
 * Task-Deal association class.
 *
 * @ORM_Mapping\Entity
 */
class TaskAssociatedDeal extends TaskAssociation
{
  
  /**
   * @var Application\DeskPRO\Entity\Deal
   * @ORM_Mapping\ManyToOne(targetEntity="Deal", inversedBy="task_associations")
   * @ORM_Mapping\JoinColumn(name="deal_id", referencedColumnName="id", onDelete="cascade")
   */
  protected $deal;
  
}
