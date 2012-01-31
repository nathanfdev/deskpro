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
 * Deal entity definition
 *
 * @ORM_Mapping\Entity(repositoryClass="Application\DeskPRO\EntityRepository\DealTypeStage")
 * @ORM_Mapping\Table(name="deal_type_stage")
 */


class DealTypeStage extends \Application\DeskPRO\Domain\DomainObject
{

    /**
     * The unique ID
     *
     * @var int
     * @ORM_Mapping\Id
     * @ORM_Mapping\generatedValue(strategy="IDENTITY")
     * @ORM_Mapping\Column(name="id", type="integer")
     *
     */
    protected $id = null;

    /**
     * @var \Application\DeskPRO\Entity\DealType
     * @ORM_Mapping\ManyToOne(targetEntity="DealType", inversedBy="deal_type_stage")
     * @ORM_Mapping\JoinColumn(name="deal_type_id", referencedColumnName="id", onDelete="cascade")
     */
    protected $deal_type;

    /**
     * @var \Application\DeskPRO\Entity\DealStage
     * @ORM_Mapping\ManyToOne(targetEntity="DealStage", inversedBy="deal_type_stage")
     * @ORM_Mapping\JoinColumn(name="deal_stage_id", referencedColumnName="id", onDelete="cascade")
     */
    protected $deal_stage;

    /**
     * @var int
     * @ORM_Mapping\Column(name="display_order", type="integer")
     */
    protected $display_order = 0;

    /**
     * Creates a new deal type
     */
    public function __construct()
    {
        $this->custom_data = new \Doctrine\Common\Collections\ArrayCollection();
    }

}
