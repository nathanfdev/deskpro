<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\Entity;

use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\ClassMetadataInfo;

class DownloadsSubscription extends \Application\DeskPRO\Domain\DomainObject
{
    /**
     * @var int
     */
    protected $id;

    /**
     * @var \Application\DeskPRO\Entity\Person
     */
    protected $person;

    /**
     * @var \Application\DeskPRO\Entity\DownloadCategory
     */
    protected $category;

    /**
     * @var \Application\DeskPRO\Entity\Download
     */
    protected $download;

    /**
     * @var bool
     */
    protected $root_category;

    /**
     * @param DownloadCategory $category
     */
    public function setCategory(DownloadCategory $category = null)
    {
        if ($category) {
            $this->setModelField('download', null);
        }

        $this->setModelField('category', $category);
    }

    /**
     * @param Download $download
     */
    public function setDownload(Download $download = null)
    {
        if ($download) {
            $this->setModelField('category', null);
        }

        $this->setModelField('download', $download);
    }

    /**
     * @return bool
     */
    public function isRootCategory()
    {
        return $this->root_category;
    }

    /**
     * @param bool $root_category
     */
    public function setRootCategory($root_category)
    {
        $this->setModelField('root_category', $root_category);
    }

    //###########################################################################
    // Doctrine Metadata
    //###########################################################################

    public static function loadMetadata(ClassMetadata $metadata)
    {
        $metadata->setInheritanceType(ClassMetadataInfo::INHERITANCE_TYPE_NONE);
        $metadata->customRepositoryClassName = 'Application\DeskPRO\EntityRepository\DownloadSubscription';
        $metadata->setPrimaryTable([
            'name'    => 'download_subscriptions',
            'indexes' => [
                'root_category_idx' => ['columns' => ['root_category']],
            ],
        ]);
        $metadata->setChangeTrackingPolicy(ClassMetadataInfo::CHANGETRACKING_NOTIFY);
        $metadata->setIdGeneratorType(ClassMetadataInfo::GENERATOR_TYPE_IDENTITY);

        $metadata->mapField(
            [
                'fieldName'  => 'id',
                'type'       => 'integer',
                'precision'  => 0,
                'scale'      => 0,
                'nullable'   => false,
                'columnName' => 'id',
                'id'         => true,
            ]
        );

        $metadata->mapManyToOne(
            [
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
                'dpApi' => true,
            ]
        );

        $metadata->mapManyToOne(
            [
                'fieldName'    => 'download',
                'targetEntity' => 'Application\\DeskPRO\\Entity\\Download',
                'mappedBy'     => null,
                'inversedBy'   => null,
                'joinColumns'  => [
                    0 => [
                        'name'                 => 'download_id',
                        'referencedColumnName' => 'id',
                        'nullable'             => true,
                        'onDelete'             => 'cascade',
                        'columnDefinition'     => null,
                    ],
                ],
            ]
        );

        $metadata->mapManyToOne(
            [
                'fieldName'    => 'category',
                'targetEntity' => 'Application\\DeskPRO\\Entity\\DownloadCategory',
                'mappedBy'     => null,
                'inversedBy'   => null,
                'joinColumns'  => [
                    0 => [
                        'name'                 => 'category_id',
                        'referencedColumnName' => 'id',
                        'nullable'             => true,
                        'onDelete'             => 'cascade',
                        'columnDefinition'     => null,
                    ],
                ],
                'dpApi' => true,
            ]
        );

        $metadata->mapField(
            [
                'fieldName'  => 'root_category',
                'type'       => 'boolean',
                'precision'  => 0,
                'scale'      => 0,
                'nullable'   => true,
                'columnName' => 'root_category',
            ]
        );
    }

    /**
     * @return DownloadCategory
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * @return Download
     */
    public function getDownload()
    {
        return $this->download;
    }

    /**
     * @return Person
     */
    public function getPerson()
    {
        return $this->person;
    }
}
