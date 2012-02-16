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
 * @ORM_Mapping\Entity(repositoryClass="Application\DeskPRO\EntityRepository\DealStage")
 * @ORM_Mapping\Table(name="deals_stage")
 */

class DealStage extends \Application\DeskPRO\Domain\DomainObject
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
     * The Deal Stage name
     *
     * @var string
     * @ORM_Mapping\Column(name="name", type="string")
     */
    protected $name = '';

    /**
     * @var \Application\DeskPRO\Entity\DealTypeStage
     * @ORM_Mapping\OneToMany(targetEntity="DealTypeStage", mappedBy="deal_stage", cascade={"persist", "remove", "merge"}, orphanRemoval=true)
     *
     */
    protected $deal_type_stage;

//    /**
//     * @var int
//     * @ORM_Mapping\Column(name="display_order", type="integer")
//     */
//    protected $display_order = 0;

}