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
 */

namespace Application\DeskPRO\Entity;

use Application\DeskPRO\App;
use Application\DeskPRO\Domain\DomainObject;
use Application\DeskPRO\Translate\HasPhraseName;
use Application\DeskPRO\Translate\Translate;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\ClassMetadataInfo;

/**
 * A custom field definition
 *
 */
class CustomFieldDefinition extends DomainObject implements HasPhraseName
{
    /**
     * The unique ID.
     *
     * @var int
     */
    protected $id = null;

    /**
     * Is the field associated with an app?
     * These generally cant be edited.
     *
     * @var \Application\DeskPRO\Entity\AppInstance
     */
    protected $app;

    /**
     * JS class to init
     * todo remove unused?
     *
     * @var string
     */
    protected $js_class = '';

    /**
     * True if this field uses a custom template when rendering the form input
     * todo remove unused?
     *
     * @var string
     */
    protected $has_form_template = false;

    /**
     * True i this field uses a custom template when rendering the form value for display
     * todo remove unused?
     *
     * @var string
     */
    protected $has_display_template = false;

    /**
     * The title
     *
     * @var string
     */
    protected $title;

    /**
     * The description
     *
     * @var string
     */
    protected $description;

    /**
     * Options for the field
     *
     */
    protected $options;

    /**
     * Can the field be viewed by the user?
     *
     * @var string
     */
    protected $is_user_enabled;

    /**
     * @var string
     */
    protected $is_enabled;

    /**
     * @var int
     */
    protected $display_order;

    /**
     * @var string
     */
    protected $default_value;

    /**
     * @var string
     */
    protected $is_agent_field;

    /**
     * @var CustomFieldDefinition
     */
    protected $parent;

    /**
     * @var \Doctrine\Common\Collections\ArrayCollection
     */
    protected $children;

    /**
     * the class of Form Type
     *
     * @var string
     */
    protected $form_type;

    /**
     * the classname of the owner of the custom field (Ticket, Person, etc)
     *
     * @var string
     */
    protected $owner_class;

    /**
     * @var string
     */
    protected $context_class;

    /**
     * @var int
     */
    protected $context_id;

    public function __construct()
    {
        $this->children = new ArrayCollection();
        $this->description = '';
        $this->display_order = 0;
        $this->options = array();
        $this->is_enabled = true;
        $this->is_user_enabled = true;
        $this->is_agent_field = false;
    }

    /**
     * @return \Application\DeskPRO\Form\Type\CustomFields\CustomFieldType
     */
    public function createType()
    {
        $class = $this->form_type;

        return new $class($this);
    }

    /**
     * @return \Application\DeskPRO\Form\Type\CustomFields\Definitions\CustomFieldDefinitionType
     * @throws \Exception
     */
    public function createDefinitionType()
    {
        if (!$this->form_type) {
            throw new \Exception('form_type must be specified');
        }

        $type = substr($this->form_type, strrpos($this->form_type, '\\') + 1);
        $class = 'Application\DeskPRO\Form\Type\CustomFields\Definitions\\' . str_replace('Type', 'DefinitionType', $type);

        return new $class;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return App::getTranslator()->getPhraseObject($this, 'title');
    }


    /**
     * @return string
     */
    public function getRealTitle()
    {
        return $this->title;
    }


    /**
     * @return string
     */
    public function getDescription()
    {
        return App::getTranslator()->getPhraseObject($this, 'description');
    }


    /**
     * @return string
     */
    public function getRealDescription()
    {
        return $this->description;
    }

    /**
     * @param  null      $property
     * @param  Translate $translate
     * @return string
     */
    public function getPhraseName($property = null, Translate $translate)
    {
        if (!$property) {
            $property = 'title';
        }

        $name = strtolower(\Orb\Util\Util::getBaseClassname($this));

        $phrase_name = 'obj_'.$name.'.' . $this->id . '_' . $property;

        return $phrase_name;
    }

    /**
     * @param  null      $property
     * @param  Translate $translate
     * @return string
     */
    public function getPhraseDefault($property = null, Translate $translate)
    {
        if ($property == 'description') {
            return $this->description;
        }

        return $this->title;
    }




    ############################################################################
    # Doctrine Metadata
    ############################################################################

    public static function loadMetadata(ClassMetadata $metadata)
    {
        $metadata->setPrimaryTable(array(
            'name' => 'custom_field_definition',
            'indexes' => array(
                'context_idx' => array('columns' => array('context_class', 'context_id'))
            )
        ));
        $metadata->customRepositoryClassName = 'Application\DeskPRO\EntityRepository\CustomFieldDefinition';
        $metadata->setInheritanceType(ClassMetadataInfo::INHERITANCE_TYPE_NONE);
        $metadata->setChangeTrackingPolicy(ClassMetadataInfo::CHANGETRACKING_NOTIFY);
        $metadata->setIdGeneratorType(ClassMetadataInfo::GENERATOR_TYPE_IDENTITY);

        $metadata->mapField(array( 'fieldName' => 'id', 'type' => 'integer', 'nullable' => false, 'columnName' => 'id', 'id' => true, ));

        // todo is these columns required?
        $metadata->mapField(array( 'fieldName' => 'js_class', 'type' => 'string', 'nullable' => false, 'columnName' => 'js_class', ));
        $metadata->mapField(array( 'fieldName' => 'has_form_template', 'type' => 'boolean', 'nullable' => false, 'columnName' => 'has_form_template', ));
        $metadata->mapField(array( 'fieldName' => 'has_display_template', 'type' => 'boolean', 'nullable' => false, 'columnName' => 'has_display_template', ));

        $metadata->mapField(array( 'fieldName' => 'title', 'type' => 'string', 'nullable' => false, 'columnName' => 'title', ));
        $metadata->mapField(array( 'fieldName' => 'description', 'type' => 'text', 'nullable' => false, 'columnName' => 'description', ));
        $metadata->mapField(array( 'fieldName' => 'options', 'type' => 'array', 'nullable' => false, 'columnName' => 'options', ));
        $metadata->mapField(array( 'fieldName' => 'default_value', 'type' => 'string', 'length' => 500, 'nullable' => true, 'columnName' => 'default_value', ));
        $metadata->mapField(array( 'fieldName' => 'display_order', 'type' => 'integer', 'nullable' => false, 'columnName' => 'display_order', ));

        $metadata->mapField(array( 'fieldName' => 'is_enabled', 'type' => 'boolean', 'nullable' => false, 'columnName' => 'is_enabled', ));
        $metadata->mapField(array( 'fieldName' => 'is_user_enabled', 'type' => 'boolean', 'nullable' => false, 'columnName' => 'is_user_enabled', ));
        $metadata->mapField(array( 'fieldName' => 'is_agent_field', 'type' => 'boolean', 'nullable' => false, 'columnName' => 'is_agent_field', ));

        $metadata->mapField(array( 'fieldName' => 'form_type', 'type' => 'string', 'nullable' => false, 'columnName' => 'form_type'));
        $metadata->mapField(array( 'fieldName' => 'owner_class', 'type' => 'string', 'nullable' => false, 'columnName' => 'owner_class'));
        $metadata->mapField(array( 'fieldName' => 'context_class', 'type' => 'string', 'nullable' => true, 'columnName' => 'context_class'));
        $metadata->mapField(array( 'fieldName' => 'context_id', 'type' => 'integer', 'nullable' => true, 'columnName' => 'context_id'));

        $metadata->mapManyToOne(array(
            'fieldName' => 'parent',
            'targetEntity' => 'Application\\DeskPRO\\Entity\\CustomFieldDefinition',
            'mappedBy' => NULL,
            'inversedBy' => 'children',
            'joinColumns' => array(
                0 => array(
                    'name' => 'parent_id',
                    'referencedColumnName' => 'id',
                    'nullable' => true,
                    'onDelete' => 'cascade',
                ),
            ),
        ));

        $metadata->mapOneToMany(array(
            'fieldName' => 'children',
            'targetEntity' => 'Application\\DeskPRO\\Entity\\CustomFieldDefinition',
            'cascade' => array('remove', 'persist', 'merge'),
            'mappedBy' => 'parent',
            'orderBy' => array(
                'display_order' => 'ASC',
            ),
        ));

        $metadata->mapManyToOne(array(
            'fieldName' => 'app',
            'targetEntity' => 'Application\\DeskPRO\\Entity\\AppInstance',
            'mappedBy' => NULL,
            'inversedBy' => NULL,
            'joinColumns' => array(
                0 => array(
                    'name' => 'app_id',
                    'referencedColumnName' => 'id',
                    'unique' => false,
                    'nullable' => true,
                    'onDelete' => 'set null',
                    'columnDefinition' => NULL,
                ),
            ),
        ));
    }
}
