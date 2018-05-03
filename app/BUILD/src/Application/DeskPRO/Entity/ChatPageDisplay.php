<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\Entity;

use Application\DeskPRO\App;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\ClassMetadataInfo;

/**
 * Description for a section within the ticket page.
 *
 * @see \Application\DeskPRO\PageDisplay\Zone\BasicZone
 */
class ChatPageDisplay extends PageDisplayAbstract
{
    const ZONE_AGENT = 'agent';
    const ZONE_USER  = 'user';

    const SECTION_DEFAULT = 'default';

    /**
     * Where this element description applies. Examples:
     * - agent
     * - user.
     *
     * @var string
     */
    protected $zone;

    /**
     * @var \Application\DeskPRO\Entity\Department
     */
    protected $department = null;

    /**
     * @var array
     */
    protected $options = [];

    /**
     * Set the department id.
     *
     * @param int $id
     */
    public function setDepartmentId($id)
    {
        if (!$id) {
            $this->setModelField('department', 0);
        } else {
            $this->setModelField('department', App::getEntityRepository('DeskPRO:Department')->find($id));
        }
    }

    /**
     * Get the department id.
     *
     * @return int
     */
    public function getDepartmentId()
    {
        if (!$this->department) {
            return 0;
        }

        return $this->department['id'];
    }

    /**
     * Get an option.
     *
     * @param  $name
     * @param null $default
     *
     * @return array|null
     */
    public function getOption($name, $default = null)
    {
        return isset($this->options[$name]) ? $this->options[$name] : $default;
    }

    /**
     * Set an option.
     *
     * @param  $name
     * @param  $value
     */
    public function setOption($name, $value)
    {
        $old                  = $this->options;
        $this->options[$name] = $value;
        $this->_onPropertyChanged('options', $old, $this->options);
    }

    public function setData(array $data)
    {
        $d = [];

        foreach ($data as $k => $item_data) {
            $m = null;
            if (preg_match('#^(.*?)\[(.*?)\]$#', $item_data['id'], $m)) {
                $item_data['field_type'] = $m[1];
                $item_data['field_id']   = $m[2];
            } else {
                $item_data['field_type'] = $item_data['id'];
            }

            $d[$k] = $item_data;
        }

        $this->setModelField('data', $d);
    }

    //###########################################################################
    // Doctrine Metadata
    //###########################################################################

    public static function loadMetadata(ClassMetadata $metadata)
    {
        $metadata->setInheritanceType(ClassMetadataInfo::INHERITANCE_TYPE_NONE);
        $metadata->customRepositoryClassName = 'Application\DeskPRO\EntityRepository\ChatPageDisplay';
        $metadata->setPrimaryTable(['name' => 'chat_page_display']);
        $metadata->setChangeTrackingPolicy(ClassMetadataInfo::CHANGETRACKING_NOTIFY);
        $metadata->mapField([
            'fieldName'  => 'zone',
            'type'       => 'string',
            'length'     => 50,
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => false,
            'columnName' => 'zone',
        ]);
        $metadata->mapField([
            'fieldName'  => 'options',
            'type'       => 'array',
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => false,
            'columnName' => 'options',
        ]);
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
            'fieldName'  => 'section',
            'type'       => 'string',
            'length'     => 50,
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => false,
            'columnName' => 'section',
        ]);
        $metadata->mapField([
            'fieldName'  => 'data',
            'type'       => 'array',
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => false,
            'columnName' => 'data',
        ]);
        $metadata->setIdGeneratorType(ClassMetadataInfo::GENERATOR_TYPE_IDENTITY);
        $metadata->mapManyToOne([
            'fieldName'    => 'department',
            'targetEntity' => 'Application\\DeskPRO\\Entity\\Department',
            'mappedBy'     => null,
            'inversedBy'   => null,
            'joinColumns'  => [
                0 => [
                    'name'                 => 'department_id',
                    'referencedColumnName' => 'id',
                    'nullable'             => true,
                    'onDelete'             => 'cascade',
                    'columnDefinition'     => null,
                ],
            ],
        ]);
    }
}
