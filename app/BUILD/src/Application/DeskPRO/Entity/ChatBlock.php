<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\Entity;

use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\ClassMetadataInfo;

class ChatBlock extends \Application\DeskPRO\Domain\DomainObject
{
    /**
     * @var int
     */
    protected $id = null;

    /**
     * @var string|null
     */
    protected $visitor_id = null;

    /**
     * @var string
     */
    protected $ip_address = '';

    /**
     * @var string
     */
    protected $reason = '';

    /**
     * @var \Application\DeskPRO\Entity\Person
     */
    protected $by_person = '';

    /**
     * @var \DateTime
     */
    protected $date_created;

    public function __construct()
    {
        $this->date_created = new \DateTime();
    }

    /**
     * @return string
     */
    public function getIpAddress()
    {
        return $this->ip_address;
    }

    /**
     * @param string $ip
     */
    public function setIpAddress($ip)
    {
        $this->setModelField('ip_address', $ip);
    }

    /**
     * @return null|string
     */
    public function getVisitorId()
    {
        return $this->visitor_id;
    }

    /**
     * @param $id
     */
    public function setVisitorId($id)
    {
        $this->setModelField('visitor_id', $id);
    }

    public static function loadMetadata(ClassMetadata $metadata)
    {
        $metadata->setInheritanceType(ClassMetadataInfo::INHERITANCE_TYPE_NONE);
        $metadata->setChangeTrackingPolicy(ClassMetadataInfo::CHANGETRACKING_NOTIFY);
        $metadata->setIdGeneratorType(ClassMetadataInfo::GENERATOR_TYPE_IDENTITY);
        $metadata->customRepositoryClassName = 'Application\DeskPRO\EntityRepository\ChatBlock';
        $metadata->setPrimaryTable([
            'name' => 'chat_blocks',
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
        $metadata->mapField(
            [
                'fieldName'  => 'visitor_id',
                'type'       => 'string',
                'length'     => 120,
                'precision'  => 0,
                'scale'      => 0,
                'nullable'   => true,
                'columnName' => 'visitor_id',
            ]
        );
        $metadata->mapField([
            'fieldName'  => 'ip_address',
            'type'       => 'string',
            'length'     => 255,
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => false,
            'columnName' => 'ip_address',
        ]);
        $metadata->mapField([
            'fieldName'  => 'reason',
            'type'       => 'text',
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => false,
            'columnName' => 'reason',
        ]);
        $metadata->mapField([
            'fieldName'  => 'date_created',
            'type'       => 'datetime',
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => false,
            'columnName' => 'date_created',
        ]);
        $metadata->mapManyToOne([
            'fieldName'    => 'by_person',
            'targetEntity' => 'Application\\DeskPRO\\Entity\\Person',
            'mappedBy'     => null,
            'inversedBy'   => null,
            'joinColumns'  => [
                0 => [
                    'name'                 => 'by_person_id',
                    'referencedColumnName' => 'id',
                    'nullable'             => true,
                    'onDelete'             => 'set null',
                    'columnDefinition'     => null,
                ],
            ],
        ]);
    }
}
