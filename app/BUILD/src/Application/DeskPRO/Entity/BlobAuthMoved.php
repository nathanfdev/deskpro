<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\Entity;

use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\ClassMetadataInfo;

class BlobAuthMoved extends \Application\DeskPRO\Domain\DomainObject
{
    /**
     * @var int
     */
    protected $id = null;

    /**
     * @var string
     */
    protected $old_authcode;

    /**
     * @var string
     */
    protected $new_authcode;

    /**
     * @var string
     */
    protected $filename;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getOldAuthcode()
    {
        return $this->old_authcode;
    }

    /**
     * @param string $old_authcode
     */
    public function setOldAuthcode($old_authcode)
    {
        $this->setModelField('old_authcode', $old_authcode);
    }

    /**
     * @return string
     */
    public function getNewAuthcode()
    {
        return $this->new_authcode;
    }

    /**
     * @param string $new_authcode
     */
    public function setNewAuthcode($new_authcode)
    {
        $this->setModelField('new_authcode', $new_authcode);
    }

    /**
     * @return string
     */
    public function getFilename()
    {
        return $this->filename;
    }

    /**
     * @param string $filename
     */
    public function setFilename($filename)
    {
        $this->setModelField('filename', $filename);
    }

    //###########################################################################
    // Doctrine Metadata
    //###########################################################################

    public static function loadMetadata(ClassMetadata $metadata)
    {
        $metadata->setInheritanceType(ClassMetadataInfo::INHERITANCE_TYPE_NONE);
        $metadata->setPrimaryTable([
            'name'    => 'blobs_auth_moved',
            'indexes' => [
                'authcode_idx' => ['columns' => ['old_authcode']],
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
            'fieldName'  => 'old_authcode',
            'type'       => 'string',
            'length'     => 50,
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => false,
            'columnName' => 'old_authcode',
        ]);
        $metadata->mapField([
            'fieldName'  => 'new_authcode',
            'type'       => 'string',
            'length'     => 50,
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => false,
            'columnName' => 'new_authcode',
        ]);
        $metadata->mapField([
            'fieldName'  => 'filename',
            'type'       => 'string',
            'length'     => 255,
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => false,
            'columnName' => 'filename',
        ]);
        $metadata->setIdGeneratorType(ClassMetadataInfo::GENERATOR_TYPE_IDENTITY);
    }
}
