<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\AppBundle\Entity;

use Application\DeskPRO\Entity\Person;
use Doctrine\Common\NotifyPropertyChanged;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;

/**
 * @ORM\Entity(repositoryClass="DeskPRO\Bundle\AppBundle\Entity\Repository\TicketFilterViewRepository")
 * @ORM\Table(name="ticket_filter_views")
 * @JMS\ExclusionPolicy("all")
 * @ORM\ChangeTrackingPolicy("NOTIFY")
 */
class TicketFilterView implements EntityInterface, NotifyPropertyChanged
{
    use NotifyPropertyChangedTrait;

    const TYPE_LIST  = 'list';
    const TYPE_TABLE = 'table';

    /**
     * The unique ID.
     *
     * @ORM\Id()
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue()
     *
     * @JMS\Expose()
     * @JMS\Type("integer")
     */
    protected $id;

    /**
     * Title for this view.
     *
     * @ORM\Column(name="title", type="string", length=64)
     *
     * @JMS\Expose()
     * @JMS\Type("string")
     *
     * @var string
     */
    protected $title;

    /**
     * View type.
     *
     * @ORM\Column(name="type", type="string", length=10)
     *
     * @JMS\Expose()
     * @JMS\Type("string")
     *
     * @var string
     */
    protected $type;

    /**
     * Filter entity.
     *
     * @ORM\ManyToOne(targetEntity="DeskPRO\Bundle\AppBundle\Entity\TicketFilter", inversedBy="filter_views")
     * @JMS\Expose()
     * @JMS\Type("entity<DeskPRO\Bundle\AppBundle\Entity\TicketFilter>")
     *
     * @var TicketFilter
     */
    protected $filter;

    /**
     * Person owner of view.
     *
     * @ORM\ManyToOne(targetEntity="Application\DeskPRO\Entity\Person", cascade={"remove"})
     * @ORM\JoinColumn(name="person_id")
     *
     * @JMS\Expose()
     * @JMS\Type("entity<Application\DeskPRO\Entity\Person>")
     *
     * @var Person
     */
    protected $agent;

    /**
     * Fields used by this view.
     *
     * @ORM\Column(name="fields", type="json_array")
     *
     * @JMS\Expose()
     * @JMS\Type("array")
     *
     * @var array
     */
    protected $fields;

    /**
     * Icon for fields.
     *
     * @ORM\Column(name="icon_fields", type="json_array")
     *
     * @JMS\Expose()
     * @JMS\Type("array")
     *
     * @var array
     */
    protected $icon_fields;

    /**
     * Additional view options.
     *
     * @ORM\Column(name="options", type="json_array")
     *
     * @JMS\Expose()
     * @JMS\Type("array")
     *
     * @var array
     */
    protected $options;

    /**
     * Display order of view.
     *
     * @ORM\Column(name="display_order", type="integer")
     *
     * @JMS\Expose()
     * @JMS\Type("integer")
     *
     * @var int
     */
    protected $display_order;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->setModelField('type', self::TYPE_LIST);
        $this->setModelField('icon_fields', []);
        $this->setModelField('options', []);
        $this->setModelField('fields', []);
    }

    /**
     * @return int|null
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return bool
     */
    public function isPrivate()
    {
        return null !== $this->agent;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $type
     *
     * @return $this
     */
    public function setType($type)
    {
        if (!in_array($type, [self::TYPE_LIST, self::TYPE_TABLE])) {
            throw new \InvalidArgumentException(sprintf('"%s" is not a valid filter view type', $type));
        }

        $this->setModelField('type', $type);

        return $this;
    }

    /**
     * @return TicketFilter
     */
    public function getFilter()
    {
        return $this->filter;
    }

    /**
     * @param TicketFilter $filter
     *
     * @return $this
     */
    public function setFilter(TicketFilter $filter = null)
    {
        $this->filter = $filter;
        $this->setModelField('filter', $filter);
        $filter->addFilterView($this);

        return $this;
    }

    /**
     * @return Person
     */
    public function getAgent()
    {
        return $this->agent;
    }

    /**
     * @param Person $agent
     *
     * @return $this
     */
    public function setAgent(Person $agent = null)
    {
        $this->setModelField('agent', $agent);

        return $this;
    }

    /**
     * @return array
     */
    public function getFields()
    {
        return $this->fields;
    }

    /**
     * @param array $fields
     *
     * @return $this
     */
    public function setFields(array $fields = [])
    {
        $this->setModelField('fields', $fields);

        return $this;
    }

    /**
     * @return array
     */
    public function getIconFields()
    {
        return $this->icon_fields;
    }

    /**
     * @param array $icon_fields
     *
     * @return $this
     */
    public function setIconFields(array $icon_fields = [])
    {
        $this->setModelField('icon_fields', $icon_fields);

        return $this;
    }

    /**
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * @param array $options
     *
     * @return $this
     */
    public function setOptions(array $options = [])
    {
        $this->setModelField('options', $options);

        return $this;
    }

    /**
     * @param string $key
     * @param mixed  $val
     *
     * @return $this
     */
    public function addOption($key, $val)
    {
        $this->options[$key] = $val;
        $this->setModelField('options', $this->options);

        return $this;
    }

    /**
     * @param string $key
     *
     * @return $this
     */
    public function removeOption($key)
    {
        if (array_key_exists($key, $this->options)) {
            unset($this->options[$key]);
        }
        $this->setModelField('options', $this->options);

        return $this;
    }

    /**
     * @param string $field
     *
     * @return $this
     */
    public function addIconField($field)
    {
        if (!in_array($field, $this->icon_fields)) {
            $this->icon_fields[] = $field;
        }

        $this->setModelField('icon_fields', $this->icon_fields);

        return $this;
    }

    /**
     * @param string $field
     *
     * @return $this
     */
    public function removeIconField($field)
    {
        if (in_array($field, $this->icon_fields)) {
            $this->icon_fields = array_values(
                array_filter(
                    $this->icon_fields,
                    function ($val) use ($field) {
                        return $field != $val;
                    }
                )
            );
        }

        $this->setModelField('icon_fields', $this->icon_fields);

        return $this;
    }

    /**
     * @param string $field
     *
     * @return $this
     */
    public function addField($field)
    {
        if (!in_array($field, $this->fields)) {
            $this->fields[] = $field;
        }

        $this->setModelField('fields', $this->fields);

        return $this;
    }

    /**
     * @param string $field
     *
     * @return $this
     */
    public function removeField($field)
    {
        if (in_array($field, $this->fields)) {
            $this->fields = array_values(
                array_filter(
                    $this->fields,
                    function ($val) use ($field) {
                        return $field != $val;
                    }
                )
            );
        }

        $this->setModelField('fields', $this->fields);

        return $this;
    }
}
