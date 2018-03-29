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
 * A simple DB table cache for k=>v.
 */
class Cache extends \Application\DeskPRO\Domain\DomainObject
{
    /**
     * @var int
     */
    protected $id = null;

    /**
     * @var string
     */
    protected $data = [];

    /**
     * @var \DateTime
     */
    protected $date_expire;

    public function setData($data)
    {
        if (!is_array($data)) {
            $data = ['VALUE' => $data];
        }

        $this->setModelField('data', $data);
    }

    public function getData()
    {
        if (isset($this->data['VALUE'])) {
            return $this->data['VALUE'];
        }

        return $this->data;
    }

    /**
     * Default time to compare is "new DateTime('now')", but you can provide a date.
     *
     * @param \DateTime $now
     *
     * @return bool
     */
    public function isExpired(\DateTime $now = null)
    {
        if (null === $now) {
            $now = new \DateTime();
        }

        return $this->date_expire <= $now;
    }

    /**
     * @return \DateTime
     */
    public function getExpiresAt()
    {
        return $this->date_expire;
    }

    /**
     * @return \DateTime
     */
    public function setExpiresAt(\DateTime $expire)
    {
        $this->setModelField('date_expire', $expire);
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $id
     */
    public function setId($id)
    {
        $this->setModelField('id', $id);
    }

    //###########################################################################
    // Doctrine Metadata
    //###########################################################################

    public static function loadMetadata(ClassMetadata $metadata)
    {
        $metadata->setInheritanceType(ClassMetadataInfo::INHERITANCE_TYPE_NONE);
        $metadata->customRepositoryClassName = 'Application\DeskPRO\EntityRepository\Cache';
        $metadata->setPrimaryTable([
            'name'    => 'cache',
            'indexes' => [
                'date_expire_idx' => ['columns' => ['date_expire']],
            ],
        ]);
        $metadata->setChangeTrackingPolicy(ClassMetadataInfo::CHANGETRACKING_NOTIFY);
        $metadata->mapField([
            'fieldName'  => 'id',
            'type'       => 'string',
            'length'     => 100,
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => false,
            'columnName' => 'id',
            'id'         => true,
        ]);
        $metadata->mapField([
            'fieldName'  => 'data',
            'type'       => 'array',
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => false,
            'columnName' => 'data',
        ]);
        $metadata->mapField([
            'fieldName'  => 'date_expire',
            'type'       => 'datetime',
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => true,
            'columnName' => 'date_expire',
        ]);
    }
}
