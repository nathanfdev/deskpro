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
 * Twitter Account Search Status.
 */
class TwitterAccountSearchStatus extends \Application\DeskPRO\Domain\DomainObject
{
    /**
     * @var int
     */
    protected $id;

    /**
     * @var \Application\DeskPRO\Entity\TwitterAccountSearch
     */
    protected $search;

    /**
     * @var TwitterAccountStatus
     */
    protected $account_status;

    /**
     * @var \DateTime
     */
    protected $date_created;

    public function setAccountStatus(TwitterAccountStatus $status)
    {
        $this->setModelField('account_status', $status);
        $this->setModelField('date_created', $status->date_created);
    }

    //###########################################################################
    // Doctrine Metadata
    //###########################################################################

    public static function loadMetadata(ClassMetadata $metadata)
    {
        $metadata->setInheritanceType(ClassMetadataInfo::INHERITANCE_TYPE_NONE);
        $metadata->customRepositoryClassName = 'Application\DeskPRO\EntityRepository\Basic';
        $metadata->setPrimaryTable([
            'name'    => 'twitter_accounts_searches_statuses',
            'indexes' => [
                'search_date_idx' => [
                    'columns' => [
                        'search_id',
                        'date_created',
                    ],
                ],
            ],
        ]);
        $metadata->setChangeTrackingPolicy(ClassMetadataInfo::CHANGETRACKING_NOTIFY);
        $metadata->mapField([
            'fieldName'  => 'date_created',
            'type'       => 'datetime',
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => false,
            'columnName' => 'date_created',
        ]);
        $metadata->mapManyToOne([
            'fieldName'    => 'account_status',
            'targetEntity' => 'Application\\DeskPRO\\Entity\\TwitterAccountStatus',
            'mappedBy'     => null,
            'inversedBy'   => null,
            'joinColumns'  => [
                0 => [
                    'name'                 => 'account_status_id',
                    'referencedColumnName' => 'id',
                    'nullable'             => false,
                    'onDelete'             => 'cascade',
                    'columnDefinition'     => null,
                ],
            ],
            'id' => true,
        ]);
        $metadata->mapManyToOne([
            'fieldName'    => 'search',
            'targetEntity' => 'Application\\DeskPRO\\Entity\\TwitterAccountSearch',
            'mappedBy'     => null,
            'inversedBy'   => 'search_statuses',
            'joinColumns'  => [
                0 => [
                    'name'                 => 'search_id',
                    'referencedColumnName' => 'id',
                    'nullable'             => false,
                    'onDelete'             => 'cascade',
                    'columnDefinition'     => null,
                ],
            ],
            'id' => true,
        ]);
    }
}
