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
 * Twitter Status Tag.
 */
class TwitterStatusTag extends \Application\DeskPRO\Domain\DomainObject
{
    /**
     * @var int
     */
    protected $id;

    /**
     * @var \Application\DeskPRO\Entity\TwitterStatus
     */
    protected $status;

    /**
     * @var string
     */
    protected $hash;

    /**
     * @var int
     */
    protected $starts = 0;

    /**
     * @var int
     */
    protected $ends = 0;

    /**
     * @return int
     */
    public function getStatusId()
    {
        if (null !== $this->status) {
            return $this->status->getId();
        }

        return 0;
    }

    /**
     * @param int $id
     */
    public function setStatusId($id)
    {
        $this->setModelField('status', null);

        if ($id && $status = App::getOrm()->getRepository('DeskPRO:TwitterStatus')->find($id)) {
            $this->setModelField('status', $status);
        }
    }

    /**
     * @param object $tag
     *
     * @return \Application\DeskPRO\Entity\TwitterStatusTag
     */
    public static function createFromJson($tag)
    {
        $entity           = new self();
        $entity['hash']   = $tag->text;
        $entity['starts'] = $tag->indices[0];
        $entity['ends']   = $tag->indices[1];

        return $entity;
    }

    //###########################################################################
    // Doctrine Metadata
    //###########################################################################

    public static function loadMetadata(ClassMetadata $metadata)
    {
        $metadata->setInheritanceType(ClassMetadataInfo::INHERITANCE_TYPE_NONE);
        $metadata->customRepositoryClassName = 'Application\DeskPRO\EntityRepository\Basic';
        $metadata->setPrimaryTable(['name' => 'twitter_statuses_tags']);
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
            'fieldName'  => 'hash',
            'type'       => 'string',
            'length'     => 255,
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => false,
            'columnName' => 'hash',
        ]);
        $metadata->mapField([
            'fieldName'  => 'starts',
            'type'       => 'integer',
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => false,
            'columnName' => 'starts',
        ]);
        $metadata->mapField([
            'fieldName'  => 'ends',
            'type'       => 'integer',
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => false,
            'columnName' => 'ends',
        ]);
        $metadata->setIdGeneratorType(ClassMetadataInfo::GENERATOR_TYPE_IDENTITY);
        $metadata->mapManyToOne([
            'fieldName'    => 'status',
            'targetEntity' => 'Application\\DeskPRO\\Entity\\TwitterStatus',
            'mappedBy'     => null,
            'inversedBy'   => 'tags',
            'joinColumns'  => [
                [
                    'name'                 => 'status_id',
                    'referencedColumnName' => 'id',
                    'nullable'             => false,
                    'onDelete'             => 'cascade',
                    'columnDefinition'     => null,
                ],
            ],
        ]);
    }
}
