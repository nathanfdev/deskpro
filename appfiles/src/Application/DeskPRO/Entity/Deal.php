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
 * @ORM_Mapping\Entity(repositoryClass="Application\DeskPRO\EntityRepository\Deal")
 * @ORM_Mapping\Table(name="deals")
 */

class Deal extends \Application\DeskPRO\Domain\DomainObject
{

    /**
     * Deal open status constant.
     * @var int
     */
    const DEAL_OPEN = 0;

    /**
     * Deal won status constant.
     * @var int
     */
    const DEAL_WON = 1;

    /**
     * Deal lost constant.
     * @var int
     */
    const DEAL_LOST = 2;

    /**
     * Private visibility constant.
     * @var int
     */
    const PRIVATE_VISIBILITY = 0;

    /**
     * Public visibility constant.
     * @var int
     */
    const PUBLIC_VISIBILITY = 2;

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
     * Deal type
     *
     * @ORM_Mapping\ManyToOne(targetEntity="DealType")
     * @ORM_Mapping\JoinColumn(name="deal_type_id", referencedColumnName="id", onDelete="set null")
     */
    protected $deal_type;

    /**
     * The deal status. On of: self::DEAL_OPEN,
     * self::DEAL_WON or self::DEAL_LOST.
     *
     * @var int
     * @ORM_Mapping\Column(name="status", type="integer")
     */
    protected $status = 0;

    /**
     * @var Application\DeskPRO\Entity\Person
     * @ORM_Mapping\ManyToOne(targetEntity="Person", inversedBy="deals")
     * @ORM_Mapping\JoinColumn(name="person_id", referencedColumnName="id", onDelete="set null")
     */
    protected $person;

    /**
     * @var Application\DeskPRO\Entity\Person
     * @ORM_Mapping\ManyToOne(targetEntity="Person", inversedBy="assigned_deals")
     * @ORM_Mapping\JoinColumn(name="assigned_agent_id", referencedColumnName="id", nullable=true, onDelete="set null")
     */
    protected $assigned_agent;

    /**
     * The deal probability
     *
     * @var float
     * @ORM_Mapping\Column(name="probability", type="float")
     */
    protected $probability = 0.0;

    /**
     * The deal value
     *
     * @var float
     * @ORM_Mapping\Column(name="deal_value", type="float")
     */
    protected $deal_value = 0.0;

    /**
     * Deal Currency type
     *
     * @ORM_Mapping\ManyToOne(targetEntity="Currency")
     * @ORM_Mapping\JoinColumn(name="currency_id", referencedColumnName="id", onDelete="set null")
     */
    protected $deal_currency = null;

    /**
     * @ORM_Mapping\OneToMany(targetEntity="LabelDeal", mappedBy="deal", cascade={"persist", "remove", "merge"}, orphanRemoval=true)
     */
    protected $labels;

    /**
     * Deal will be linked to relevant to many people.
     * 
     * @ORM_Mapping\OneToMany(targetEntity="Person", mappedBy="deal_peoples", cascade={"persist", "remove", "merge"}, orphanRemoval=true)
     */
    protected $peoples;

    /**
     * Deal will be linked to relevant to many organization.
     *
     * @ORM_Mapping\OneToMany(targetEntity="Organization", mappedBy="deal_organizations", cascade={"persist", "remove", "merge"}, orphanRemoval=true)
     */
    protected $organizations;

    /**
     * @var \Doctrine\Common\Collections\ArrayCollection
     * @ORM_Mapping\OneToMany(targetEntity="TaskAssociatedDeal", mappedBy="person")
     */
    protected $task_associations;

    /**
     * The task's visibility. On of: self::PRIVATE_VISIBILITY
     * or self::PUBLIC_VISIBILITY.
     *
     * @var int
     * @ORM_Mapping\Column(name="visibility", type="integer")
     */
    protected $visibility = 0;

    /**
     * @var \DateTime
     * @ORM_Mapping\Column(name="date_created",type="datetime")
     */
    protected $date_created;

    /**
     * Creates a new deal
     */
    public function __construct()
    {
        $this->labels            = new \Doctrine\Common\Collections\ArrayCollection();
        $this->task_associations = new \Doctrine\Common\Collections\ArrayCollection();
        $this->peoples = new \Doctrine\Common\Collections\ArrayCollection();
        $this->organizations = new \Doctrine\Common\Collections\ArrayCollection();
        $this->task_associations      = new \Doctrine\Common\Collections\ArrayCollection();


        $this->date_created = new \DateTime();
    }
    

}