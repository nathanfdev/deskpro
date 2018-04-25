<?php

namespace DeskPRO\Bundle\AppBundle\Serializer\Model;

use Application\DeskPRO\Entity\CustomDefAbstract;
use DeskPRO\Bundle\AppBundle\ObjectAlias\ObjectAliasInterface;
use DeskPRO\Component\Util\ListUtils;
use JMS\Serializer\Annotation as JMS;

/**
 * Class CustomDef.
 */
class CustomDef
{
    /**
     * The unique ID.
     *
     * @var int
     *
     * @JMS\Type("integer")
     */
    private $id;

    /**
     * @var CustomDefAbstract
     *
     * @JMS\Type("entity<Application\DeskPRO\Entity\CustomDefAbstract>")
     */
    private $parent;

    /**
     * The title.
     *
     * @var string
     *
     * @JMS\Type("string")
     */
    private $title;

    /**
     * The description.
     *
     * @var string
     *
     * @JMS\Type("string")
     */
    private $description;

    /**
     * Options for the field.
     */
    private $options;

    /**
     * Can the field be viewed by the user?
     *
     * @var bool
     *
     * @JMS\Type("boolean")
     */
    private $isUserEnabled;

    /**
     * True if field is enabled.
     *
     * @JMS\Type("boolean")
     *
     * @var bool
     */
    private $isEnabled;

    /**
     * Obviously it is field`s display order.
     *
     * @JMS\Type("integer")
     *
     * @var int
     */
    private $displayOrder;

    /**
     * Is this field associated with agents only.
     *
     * @JMS\Type("boolean")
     *
     * @var bool
     */
    private $isAgentField;

    /**
     * @JMS\Type("array")
     *
     * @var array
     */
    private $choices;

    /**
     * @var mixed
     */
    private $defaultValue;

    /**
     * @JMS\Type("string")
     *
     * @var string
     */
    private $widgetType;

    /**
     * @JMS\Type("map<DeskPRO\Bundle\AppBundle\Serializer\Model\CustomDefTranslation>")
     *
     * @var CustomDefTranslation[]
     */
    private $translations = [];

    /**
     * @JMS\Type("array<string>")
     *
     * @var string[]
     */
    private $aliases;

    /**
     * Constructor.
     *
     * @param CustomDefAbstract $def
     */
    public function __construct(CustomDefAbstract $def)
    {
        $this->id            = $def->getId();
        $this->parent        = $def->getParent();
        $this->title         = $def->getRawTitle();
        $this->description   = $def->getRawDescription();
        $this->isUserEnabled = $def->isUserEnabled();
        $this->isEnabled     = $def->isEnabled();
        $this->displayOrder  = $def->getDisplayOrder();
        $this->isAgentField  = $def->isAgentField();
        $this->defaultValue  = $def->getDefaultValue();
        $this->widgetType    = $def->getWidgetType();
        $this->choices       = $def->getChoices();
        $this->aliases       = ListUtils::map($def->getAliases(), function (ObjectAliasInterface $a) {
            return $a->getQualifiedName();
        });

        $this->options = $def->getOptions();
        if (isset($this->options['choices'])) {
            unset($this->options['choices']);
        }
        if (!$this->options) {
            $this->options = new \ArrayObject();
        }
    }

    /**
     * @param CustomDefTranslation[] $translations
     */
    public function setTranslations($translations)
    {
        $this->translations = $translations;
    }
}
