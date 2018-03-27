<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\Entity;

use Application\DeskPRO\App;
use Application\DeskPRO\Translate\HasPhraseName;
use Application\DeskPRO\Translate\Translate;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use JMS\Serializer\Annotation as JMS;

/**
 * Products.
 *
 * @JMS\ExclusionPolicy("all")
 */
class Product extends CategoryAbstract implements HasPhraseName
{
    /**
     * @var \Doctrine\Common\Collections\ArrayCollection
     */
    protected $custom_data;

    /**
     * Parent of the product.
     *
     * @JMS\Expose()
     * @JMS\Groups("details")
     * @JMS\Type("entity<Application\DeskPRO\Entity\Product>")
     *
     * @var \Application\DeskPRO\Entity\Product
     */
    protected $parent;

    /**
     * @var \Application\DeskPRO\Entity\Product
     */
    protected $children;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->custom_data = new ArrayCollection();
    }

    /**
     * @param $title
     *
     * @return $this
     */
    public function setTitle($title)
    {
        $this->setModelField('title', $title);

        return $this;
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
     * {@inheritdoc}
     */
    public function getPhraseName($property)
    {
        if (!$property) {
            $property = 'title';
        }
        $phrase_name = 'obj_product.'.$this->id.'_'.$property;

        return $phrase_name;
    }

    /**
     * {@inheritdoc}
     */
    public function getPhraseDefault($property, Translate $translate)
    {
        if ($property == 'full') {
            return $this->getFullTitle();
        }

        return $this->title;
    }

    /**
     * @return array
     */
    public function getChildrenOrdered()
    {
        $children = $this->children->toArray();
        uasort($children, function ($a, $b) {
            if ($a->display_order == $b->display_order) {
                return 0;
            }

            return ($a->display_order < $b->display_order) ? -1 : 1;
        });

        return $children;
    }

    /**
     * Find an existing data record for a field id.
     *
     * @param int $field_id
     *
     * @return CustomDataProduct
     */
    public function getCustomDataForField($field_id)
    {
        if ($field_id instanceof CustomDefProduct) {
            $field_id = $field_id['id'];
        }

        foreach ($this->custom_data as $data) {
            if ($data['field_id'] == $field_id) {
                return $data;
            }
        }

        return;
    }

    /**
     * Set custom field data for a particular field.
     *
     * @param int   $field_id
     * @param mixed $value
     *
     * @return mixed
     */
    public function setCustomData($field_id, $value_type, $value)
    {
        $custom_data = $this->getCustomDataForField($field_id);
        $is_new      = false;

        if (!$custom_data) {
            if ($value === null) {
                return;
            }

            $is_new = true;

            $field = App::getEntityRepository('DeskPRO:CustomDefProduct')->find($field_id);
            if (!$field) {
                throw new \Exception("Invalid field_id `$field_id`");
            }
            $custom_data        = new CustomDataProduct();
            $custom_data->field = $field;
        }

        $field = $custom_data->field;
        if ($field->parent) {
            foreach ($this->custom_data as $d) {
                if ($d->field && $d->field->parent && $d->field->parent['id'] == $field->parent['id']) {
                    $this->custom_data->removeElement($d);
                    $this->_onPropertyChanged('custom_data', $this->custom_data, $this->custom_data);
                }
            }
        }

        $this->custom_data->removeElement($custom_data);

        if ($value === null) {
            $this->custom_data->removeElement($custom_data);
            $this->_onPropertyChanged('custom_data', $this->custom_data, $this->custom_data);

            return;
        }

        if ($field->getTypeName() == 'choice') {
        }

        $custom_data[$value_type] = $value;

        if ($is_new) {
            $this->addCustomData($custom_data);
        }

        if ($this->id) {
            App::getEntityRepository('DeskPRO:Cache')->delete("product_custom_fields.{$this->id}");
        }

        return $custom_data;
    }

    public function removeCustomDataForField($field)
    {
        $parent_id = null;
        $field_id  = $field['id'];
        if ($field->parent) {
            $parent_id = $field->parent['id'];
        }

        foreach ($this->custom_data as $data) {
            if ($data['field_id'] == $field_id or $data['field_id'] == $parent_id) {
                $this->custom_data->removeElement($data);
                $this->_onPropertyChanged('custom_data', $this->custom_data, $this->custom_data);
            }
        }
    }

    /**
     * Add a custom data item to this product.
     *
     * @param CustomDataProduct $data
     */
    public function addCustomData(CustomDataProduct $data)
    {
        $this->custom_data->add($data);
        $data['product'] = $this;
        $this->_onPropertyChanged('custom_data', $this->custom_data, $this->custom_data);
    }

    /**
     * Check if this product has a custom field.
     *
     * @param $field_id
     *
     * @return bool
     */
    public function hasCustomField($field_id)
    {
        foreach ($this->custom_data as $data) {
            if ($data->field['id'] == $field_id) {
                return true;
            }
        }

        foreach ($this->custom_data as $data) {
            if ($data->field->parent and $data->field->parent['id'] == $field_id) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return array
     */
    public function getFieldDisplayArray()
    {
        $field_manager = App::getContainer()->getSystemService('product_fields_manager');

        return $field_manager->getDisplayArrayForObject($this);
    }

    /**
     * @param bool  $primary
     * @param bool  $deep
     * @param array $visited
     *
     * @return array
     */
    public function toApiData($primary = true, $deep = true, array $visited = [])
    {
        $data = parent::toApiData($primary, $deep, $visited);

        if ($this->parent) {
            $data['parent_id'] = $this->parent->getId();
        } else {
            $data['parent_id'] = null;
        }

        // Render custom fields to text values
        $field_manager = App::getContainer()->getSystemService('product_fields_manager');

        $values = $field_manager->getRenderedToTextForObject($this);
        foreach ($values as $fid => $v) {
            $data["field{$fid}"] = $v['rendered'];
        }

        return $data;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->getFullTitle();
    }

    //###########################################################################
    // Doctrine Metadata
    //###########################################################################

    public static function loadMetadata(ClassMetadata $metadata)
    {
        $metadata->setInheritanceType(ClassMetadataInfo::INHERITANCE_TYPE_NONE);
        $metadata->customRepositoryClassName = 'Application\DeskPRO\EntityRepository\Product';
        $metadata->setPrimaryTable(['name' => 'products']);
        $metadata->setChangeTrackingPolicy(ClassMetadataInfo::CHANGETRACKING_NOTIFY);
        $metadata->mapField([
            'fieldName'  => 'id',
            'type'       => 'integer',
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => false,
            'columnName' => 'id',
            'id'         => true,
        ]);
        $metadata->mapField([
            'fieldName'  => 'title',
            'type'       => 'string',
            'length'     => 255,
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => false,
            'columnName' => 'title',
        ]);
        $metadata->mapField([
            'fieldName'  => 'display_order',
            'type'       => 'integer',
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => false,
            'columnName' => 'display_order',
        ]);
        $metadata->mapField([
            'fieldName'  => 'depth',
            'type'       => 'integer',
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => false,
            'columnName' => 'depth',
        ]);
        $metadata->mapField([
            'fieldName'  => 'root',
            'type'       => 'integer',
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => true,
            'columnName' => 'root',
        ]);
        $metadata->setIdGeneratorType(ClassMetadataInfo::GENERATOR_TYPE_IDENTITY);
        $metadata->mapManyToOne([
            'fieldName'    => 'parent',
            'targetEntity' => 'Application\\DeskPRO\\Entity\\Product',
            'mappedBy'     => null,
            'inversedBy'   => 'children',
            'joinColumns'  => [
                0 => [
                    'name'                 => 'parent_id',
                    'referencedColumnName' => 'id',
                    'onDelete'             => 'SET NULL',
                ],
            ],
        ]);
        $metadata->mapOneToMany([
            'fieldName'    => 'children',
            'targetEntity' => 'Application\\DeskPRO\\Entity\\Product',
            'mappedBy'     => 'parent',
            'orderBy'      => ['display_order' => 'ASC'],
        ]);
        $metadata->mapOneToMany([
            'fieldName'    => 'custom_data',
            'targetEntity' => 'Application\\DeskPRO\\Entity\\CustomDataProduct',
            'cascade'      => [
                0 => 'remove',
                1 => 'persist',
                3 => 'merge',
            ],
            'mappedBy'      => 'product',
            'orphanRemoval' => true,
            'dpApi'         => false,
        ]);
    }
}
