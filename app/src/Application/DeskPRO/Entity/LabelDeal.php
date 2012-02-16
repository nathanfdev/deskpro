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
 * Records labels on deal.
 *
 * @ORM_Mapping\Entity
 * @ORM_Mapping\Table(name="labels_deals")
 */
class LabelDeal extends LabelAssocAbstract
{
    const LABEL_TYPENAME = 'deal';

  /**
   * @var \Application\DeskPRO\Entity\Deal
   * @ORM_Mapping\Id
   * @ORM_Mapping\ManyToOne(targetEntity="Deal")
   * @ORM_Mapping\JoinColumn(name="deal_id", referencedColumnName="id", onDelete="cascade")
   */
    protected $deal;
  
}
