<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\Entity;

use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\ClassMetadataInfo;

/**
 * A log of views.
 */
class PageViewLog extends \Application\DeskPRO\Domain\DomainObject
{
    const TYPE_ARTICLE  = 1;
    const TYPE_DOWNLOAD = 2;
    const TYPE_NEWS     = 3;
    const TYPE_FEEDBACK = 4;

    const ACTION_VIEW     = 1;
    const ACTION_DOWNLOAD = 2;

    /**
     * @var int
     */
    protected $id = null;

    /**
     * @var int
     */
    protected $object_type;

    /**
     * @var int
     */
    protected $object_id;

    /**
     * @var int
     */
    protected $view_action = 1;

    /**
     * @var int
     */
    protected $person_id = null;

    /**
     * @var \DateTime
     */
    protected $date_created;

    public function __construct()
    {
        $this->setModelField('date_created', new \DateTime());
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    public function setPerson(Person $person)
    {
        $id = null;
        if ($person->getId()) {
            $id = $person->getId();
        }

        $this->setModelField('person_id', $id);

        return $this;
    }

    public function getObjectType()
    {
        return self::getObjectTypeFromTypeId($this->object_type);
    }

    /**
     * @param string|int $type
     *
     * @return $this
     */
    public function setObjectType($type)
    {
        if (!is_numeric($type)) {
            switch ($type) {
                case 'article':
                    $type = self::TYPE_ARTICLE;
                    break;
                case 'download':
                    $type = self::TYPE_DOWNLOAD;
                    break;
                case 'news':
                    $type = self::TYPE_NEWS;
                    break;
                case 'feedback':
                    $type = self::TYPE_FEEDBACK;
                    break;
            }
        }

        $this->setModelField('object_type', $type);

        return $this;
    }

    /**
     * @param int $id
     *
     * @return $this
     */
    public function setObjectId($id)
    {
        $this->setModelField('object_id', $id);

        return $this;
    }

    /**
     * @param int $action
     *
     * @return $this
     */
    public function setActionView($action)
    {
        $this->setModelField('view_action', $action);

        return $this;
    }

    public static function getObjectTypeFromTypeId($type)
    {
        switch ($type) {
            case self::TYPE_ARTICLE: return 'article';
            case self::TYPE_DOWNLOAD: return 'download';
            case self::TYPE_NEWS: return 'news';
            case self::TYPE_FEEDBACK: return 'feedback';
        }

        throw new \InvalidArgumentException("Invalid type id. Got:`$type`");
    }

    //###########################################################################
    // Doctrine Metadata
    //###########################################################################

    public static function loadMetadata(ClassMetadata $metadata)
    {
        $metadata->setInheritanceType(ClassMetadataInfo::INHERITANCE_TYPE_NONE);
        $metadata->customRepositoryClassName = 'Application\DeskPRO\EntityRepository\Basic';
        $metadata->setPrimaryTable([
            'name'    => 'page_view_log',
            'indexes' => [
                'object_idx' => [
                    'columns' => [
                        'object_type',
                        'object_id',
                    ],
                ],
                'date_created_idx' => ['columns' => ['date_created']],
            ],
        ]);
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
            'fieldName'  => 'object_type',
            'type'       => 'integer',
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => false,
            'columnName' => 'object_type',
        ]);
        $metadata->mapField([
            'fieldName'  => 'object_id',
            'type'       => 'integer',
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => false,
            'columnName' => 'object_id',
        ]);
        $metadata->mapField([
            'fieldName'  => 'view_action',
            'type'       => 'integer',
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => false,
            'columnName' => 'view_action',
        ]);
        $metadata->mapField([
            'fieldName'  => 'person_id',
            'type'       => 'integer',
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => true,
            'columnName' => 'person_id',
        ]);
        $metadata->mapField([
            'fieldName'  => 'date_created',
            'type'       => 'datetime',
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => false,
            'columnName' => 'date_created',
        ]);
        $metadata->setIdGeneratorType(ClassMetadataInfo::GENERATOR_TYPE_IDENTITY);
    }
}
