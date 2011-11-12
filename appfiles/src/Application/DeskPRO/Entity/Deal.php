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
use Application\DeskPRO\Entity\LabelDeal;
use Application\DeskPRO\App;

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
    const PUBLIC_VISIBILITY = 1;

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
     
     * @var strint
     * @ORM_Mapping\Column(name="title", type="string")
     */
    protected $title;

    /**
     * Deal type
     *
     * @ORM_Mapping\ManyToOne(targetEntity="DealType")
     * @ORM_Mapping\JoinColumn(name="deal_type_id", referencedColumnName="id", onDelete="set null")
     */
    protected $deal_type;

    /**
     * Deal type
     *
     * @ORM_Mapping\ManyToOne(targetEntity="DealStage")
     * @ORM_Mapping\JoinColumn(name="deal_stage_id", referencedColumnName="id", onDelete="set null")
     */
    protected $deal_stage;

    /**
     * The deal status. On of: self::DEAL_OPEN,
     * self::DEAL_WON or self::DEAL_LOST.
     *
     * @var int
     * @ORM_Mapping\Column(name="status", type="integer")
     */
    protected $status = 0;

    /**
     * 
     *
     * @var Application\DeskPRO\Entity\Person
     * @ORM_Mapping\ManyToOne(targetEntity="Person", inversedBy="deal")
     * @ORM_Mapping\JoinColumn(name="person_id", referencedColumnName="id", onDelete="set null")
     */
    protected $person;

    /**
     * @var Application\DeskPRO\Entity\Person
     * @ORM_Mapping\ManyToOne(targetEntity="Person")
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
     * @ORM_Mapping\OneToMany(targetEntity="DealMapper", mappedBy="deal", cascade={"persist", "remove", "merge"}, orphanRemoval=true)
     */
    protected $deal_mapper;

    /**
     * Deal Linked to Relevent peoples.
     *
     * @var \Doctrine\Common\Collections\ArrayCollection
     * @ORM_Mapping\ManyToMany(targetEntity="Person", fetch="EAGER", indexBy="id")
     * @ORM_Mapping\JoinTable(name="deal_people",
     *      joinColumns={@ORM_Mapping\JoinColumn(name="deal_id", referencedColumnName="id", onDelete="cascade")},
     *      inverseJoinColumns={@ORM_Mapping\JoinColumn(name="person_id", referencedColumnName="id", onDelete="cascade")}
     * )
     */
    protected $peoples;

    /**
     * Deal Linked to Relevent organization.
     *
     * @var \Doctrine\Common\Collections\ArrayCollection
     * @ORM_Mapping\ManyToMany(targetEntity="Organization", fetch="EAGER", indexBy="id")
     * @ORM_Mapping\JoinTable(name="deal_organizations",
     *      joinColumns={@ORM_Mapping\JoinColumn(name="deal_id", referencedColumnName="id", onDelete="cascade")},
     *      inverseJoinColumns={@ORM_Mapping\JoinColumn(name="organization_id", referencedColumnName="id", onDelete="cascade")}
     * )
     */
    protected $organizations;

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

//    /**
//     * @var \Doctrine\Common\Collections\ArrayCollection
//     * @ORM_Mapping\OneToMany(targetEntity="TwitterStatusNote", mappedBy="deal")
//     */
//    protected $twitter_status_notes;

    /**
     * Label manager for adding/removing labels
     * @var \Application\DeskPRO\Labels\LabelManager
     */
    protected $_label_manager = null;

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
        //$this->twitter_status_notes   = new \Doctrine\Common\Collections\ArrayCollection();
        $this->deal_mapper   = new \Doctrine\Common\Collections\ArrayCollection();


        $this->date_created = new \DateTime();
    }

    /**
	 * Set custom field data for a particular field.
	 *
	 * @param int $field_id
	 * @param mixed $value
	 * @return mixed
	 */
	public function setCustomData($field_id, $value_type, $value)
	{
		$custom_data = $this->getCustomDataForField($field_id);
		$is_new = false;

		if (!$custom_data) {
			if ($value === null) return null;

			$is_new = true;

			$field = App::getEntityRepository('DeskPRO:CustomDefDeal')->find($field_id);
			if (!$field) {
				throw new \Exception("Invalid field_id `$field_id`");
			}
			$custom_data = new CustomDataPerson();
			$custom_data['field'] = $field;
		}

		if ($value === null) {
			$this['custom_data']->removeElement($custom_data);
			return null;
		}

		$custom_data[$value_type] = $value;

		if ($is_new) {
			$this->addCustomData($custom_data);
		}

		return $custom_data;
	}

	/**
	 * Add a custom data item to this deal
	 *
	 * @param CustomDataDeal $data
	 */
	public function addCustomData(CustomDataDeal $data)
	{
		$this->custom_data->add($data);
		$data['deal'] = $this;
	}



	/**
	 * Render a custom field
	 *
	 * !depreciated
	 */
	public function renderCustomField($field_id, $context = 'html')
	{
		$f_def = App::getEntityRepository('DeskPRO:CustomDefDeal')->find($field_id);

		$data_structured = App::getApi('custom_fields.util')->createDataHierarchy($this->custom_data, array($f_def));

		$value = !empty($data_structured[$f_def['id']]) ? $data_structured[$f_def['id']] : null;
		$rendered = $value ? $f_def->getHandler()->renderContext($context, $value) : null;

		return $rendered;
	}


	/**
	 * Check if this ticket has a custom field.
	 *
	 * @param $field_id
	 * @return bool
	 */
	public function hasCustomField($field_id)
	{
		foreach ($this->custom_data as $data) {
			if ($data->field['id'] == $field_id) {
				return true;
			}
		}

		return false;
	}

        /**
	 * Add a label
	 * @param \Application\DeskPRO\Entity\LabelDeal $label
	 */
	public function addLabel(LabelDeal $label)
	{
		$label['deal'] = $this;
		$this->labels->add($label);
	}

        public function getLabelManager()
	{
		if ($this->_label_manager === null) {
			$this->_label_manager = new \Application\DeskPRO\Labels\LabelManager($this, 'DeskPRO:LabelDeal');
		}

		return $this->_label_manager;
	}

        /**
	 * Returns the task's assigned agent's id.
	 *
	 * @return int
	 */
	public function getAsignedAgentId()
	{
		if (! $this->assigned_agent) {
			return 0;
		}

		return $this->assigned_agent['id'];
	}



	/**
	 * Sets the task's assigned agent's id.
	 *
	 * @param int id The agent's id.
	 * @throws \InvalidArgumentException Thrown when there's no preson with that
	 *                                   id or the person is not an agent.
	 */
        public function setAsignedAgentId($id)
	{
		if(!$id || $id == null){
                    $this->assigned_agent = null;
                    return;
                }


                $agent = App::getEntityRepository('DeskPRO:Person')->find($id);

		if (! $agent) {
			throw new \InvalidArgumentException('No agent for id ' . $id);
		}

		if (! $agent->is_agent) {
			throw new \InvalidArgumentException(
				'The person with id ' . $id . ' is not an agent'
			);
		}

		$this->assigned_agent = $agent;
	}


}
