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
     * @ORM_Mapping\Column(name="name", type="string")
     */
    protected $name = '';

    /**
     * Usergroups the user belongs to
     *
     * @var \Doctrine\Common\Collections\ArrayCollection
     * @ORM_Mapping\ManyToMany(targetEntity="CustomDefDeal", fetch="EAGER", indexBy="id")
     * @ORM_Mapping\JoinTable(name="deals_type_def",
     *     joinColumns={@ORM_Mapping\JoinColumn(name="deal_type_id", referencedColumnName="id", onDelete="cascade")},
     *     inverseJoinColumns={@ORM_Mapping\JoinColumn(name="deal_def_id", referencedColumnName="id", onDelete="cascade")}
     * )
     */
    protected $deal_def;

    /**
     * @ORM_Mapping\OneToMany(targetEntity="CustomDataDeal", mappedBy="ticket", cascade={"persist", "remove", "merge"}, orphanRemoval=true)
     */
    protected $custom_data;

    /**
     * @var \Application\DeskPRO\Entity\DealTypeStage
     * @ORM_Mapping\OneToMany(targetEntity="DealTypeStage", mappedBy="deal_type" , cascade={"persist", "remove", "merge"}, orphanRemoval=true)
     * 
     */
    protected $deal_type_stage;

    /**
     * Creates a new deal type
     */
    public function __construct()
    {
        $this->custom_data = new \Doctrine\Common\Collections\ArrayCollection();
    }

}