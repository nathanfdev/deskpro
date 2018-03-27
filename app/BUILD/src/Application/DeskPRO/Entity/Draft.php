<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\Entity;

use Application\DeskPRO\Domain\DomainObject;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use JMS\Serializer\Annotation as JMS;

/**
 * A draft of some sort of message.
 */
class Draft extends DomainObject
{
    /**
     * The unique ID.
     *
     * @var int
     */
    protected $id = null;

    /**
     * @var Person
     *
     * @JMS\Type("entity<Application\DeskPRO\Entity\Person>")
     */
    protected $person;

    /**
     * @var string
     */
    protected $content_type;

    /**
     * @var int
     */
    protected $content_id;

    /**
     * @var \DateTime
     * @JMS\Expose()
     * @JMS\Type("DateTime")
     */
    protected $date_created;

    /**
     * @var string
     *
     * @JMS\Expose()
     * @JMS\Type("string")
     */
    protected $message;
    /** @var string */
    protected $message_html;
    /** @var array */
    protected $extras = [];

    public function __construct()
    {
        $this->setModelField('date_created', new \DateTime());
    }

    //###########################################################################
    // Doctrine Metadata
    //###########################################################################

    public static function loadMetadata(ClassMetadata $metadata)
    {
        $metadata->setInheritanceType(ClassMetadataInfo::INHERITANCE_TYPE_NONE);
        $metadata->customRepositoryClassName = 'Application\DeskPRO\EntityRepository\Draft';
        $metadata->setPrimaryTable([
            'name'    => 'drafts',
            'indexes' => [
                'content_idx' => [
                    'columns' => [
                        'content_type',
                        'content_id',
                    ],
                ],
                'date_idx' => ['columns' => ['date_created']],
            ],
            'uniqueConstraints' => [
                'person_content_idx' => [
                    'columns' => [
                        'person_id',
                        'content_type',
                        'content_id',
                    ],
                ],
            ],
        ]);
        $metadata->setChangeTrackingPolicy(ClassMetadataInfo::CHANGETRACKING_DEFERRED_IMPLICIT);
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
            'fieldName'  => 'content_type',
            'type'       => 'string',
            'length'     => 50,
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => false,
            'columnName' => 'content_type',
        ]);
        $metadata->mapField([
            'fieldName'  => 'content_id',
            'type'       => 'integer',
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => false,
            'columnName' => 'content_id',
        ]);
        $metadata->mapField([
            'fieldName'  => 'date_created',
            'type'       => 'datetime',
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => false,
            'columnName' => 'date_created',
        ]);
        $metadata->mapField([
            'fieldName'  => 'message',
            'type'       => 'text',
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => false,
            'columnName' => 'message',
        ]);
        $metadata->mapField([
            'fieldName'  => 'message_html',
            'type'       => 'text',
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => false,
            'columnName' => 'message_html',
        ]);
        $metadata->mapField([
            'fieldName'  => 'extras',
            'type'       => 'array',
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => false,
            'columnName' => 'extras',
        ]);
        $metadata->mapManyToOne([
            'fieldName'    => 'person',
            'targetEntity' => 'Application\\DeskPRO\\Entity\\Person',
            'mappedBy'     => null,
            'inversedBy'   => null,
            'joinColumns'  => [
                0 => [
                    'name'                 => 'person_id',
                    'referencedColumnName' => 'id',
                    'nullable'             => true,
                    'onDelete'             => 'cascade',
                    'columnDefinition'     => null,
                ],
            ],
        ]);
        $metadata->setIdGeneratorType(ClassMetadataInfo::GENERATOR_TYPE_IDENTITY);
    }
}
