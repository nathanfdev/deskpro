<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. http://www.deskpro.com/   |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2012, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at http://www.deskpro.com/license                           |
|                                                                          |
| By using this software, you acknowledge having read the license          |
| and agree to be bound thereby.                                           |
|                                                                          |
| Please note that DeskPRO is not free software. We release the full       |
| source code for our software because we trust our users to pay us for    |
| the huge investment in time and energy that has gone into both creating  |
| this software and supporting our customers. By providing the source code |
| we preserve our customers' ability to modify, audit and learn from our   |
| work. We have been developing DeskPRO since 2001, please help us make it |
| another decade.                                                          |
|                                                                          |
| Like the work you see? Think you could make it better? We are always     |
| looking for great developers to join us: http://www.deskpro.com/jobs/    |
|                                                                          |
| ~ Thanks, Everyone at Team DeskPRO                                       |
\**************************************************************************/

/**
 * DeskPRO
 *
 * @package DeskPRO
 * @category Entities
 * @copyright Copyright (c) 2011 DeskPRO (http://www.deskpro.com/)
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
