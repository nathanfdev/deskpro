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
 * @ORM_Mapping\Entity(repositoryClass="Application\DeskPRO\EntityRepository\DealType")
 * @ORM_Mapping\Table(name="deals_type")
 */


class DealType extends \Application\DeskPRO\Domain\DomainObject
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
     * The Deal Type's name
     *
     * @var string
     * @ORM_Mapping\Column(name="name", type="text")
     */
    protected $name = '';

    /**
     * Usergroups the user belongs to
     *
     * @var \Doctrine\Common\Collections\ArrayCollection
     * @ORM_Mapping\ManyToMany(targetEntity="DealStage", fetch="EAGER", indexBy="id")
     * @ORM_Mapping\JoinTable(name="deal_type_stage",
     *     joinColumns={@ORM_Mapping\JoinColumn(name="deal_type_id", referencedColumnName="id", onDelete="cascade")},
     *     inverseJoinColumns={@ORM_Mapping\JoinColumn(name="deal_stage_id", referencedColumnName="id", onDelete="cascade")}
     * )
     */
    protected $deal_stage;

}