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
 * When blobs are stored in the database, they are stored as muliple parts in this table.
 *
 * (Ordering is by id ASC)
 *
 * Warning: Using blob storage records directly (e.g. to read data) is almost always wrong. DeskPRO
 * has multiple storage mechanisms (filesystem, S3) so reading from blob storage mechanism
 * means your code will only work if the db mechanism is used, which may not be the case.
 *
 * Use the DeskproBlobStorage service to read data.
 */
class BlobStorage extends \Application\DeskPRO\Domain\DomainObject
{
    /**
     * @var int
     */
    protected $id = null;

    /**
     * @var int
     */
    protected $blob_id;

    /**
     * The users name (best guess from other sources etc).
     *
     * @var string
     */
    protected $data;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return int
     */
    public function getBlobId()
    {
        return $this->blob_id;
    }

    /**
     * @return string
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param int $blob_id
     */
    public function setBlobId($blob_id)
    {
        $this->setModelField('blob_id', $blob_id);
    }

    /**
     * @param string $data
     */
    public function setData($data)
    {
        $this->setModelField('data', $data);
    }

    //###########################################################################
    // Doctrine Metadata
    //###########################################################################

    public static function loadMetadata(ClassMetadata $metadata)
    {
        $metadata->setInheritanceType(ClassMetadataInfo::INHERITANCE_TYPE_NONE);
        $metadata->setPrimaryTable([
            'name'    => 'blobs_storage',
            'indexes' => ['blob_id_idx' => ['columns' => [0 => 'blob_id']]],
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
            'fieldName'  => 'blob_id',
            'type'       => 'integer',
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => false,
            'columnName' => 'blob_id',
        ]);
        $metadata->mapField([
            'fieldName'  => 'data',
            'type'       => 'dpblob_file',
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => false,
            'columnName' => 'data',
        ]);
        $metadata->setIdGeneratorType(ClassMetadataInfo::GENERATOR_TYPE_IDENTITY);
    }
}
