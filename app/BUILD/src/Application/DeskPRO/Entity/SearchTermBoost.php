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
 * Stores which documents have been boosted, and by which terms.
 */
class SearchTermBoost extends \Application\DeskPRO\Domain\DomainObject
{
    /**
     * A 'voted' boost means the boost comes from a user
     * upvoting a particular document after coming from a search.
     */
    const METHOD_VOTE = 'vote';

    /**
     * An 'agent' boost means an agent has manually entered a boost term.
     */
    const METHOD_AGENT = 'agent';

    /**
     * @var string
     */
    protected $object_type;

    /**
     * @var int
     */
    protected $object_id = null;

    /**
     * Is this an agent bossted term?
     *
     * If not, then the b
     *
     * @var bool
     */
    protected $boosted_method = false;

    /**
     * @var string
     */
    protected $boosted_terms;

    //###########################################################################
    // Doctrine Metadata
    //###########################################################################

    public static function loadMetadata(ClassMetadata $metadata)
    {
        $metadata->setInheritanceType(ClassMetadataInfo::INHERITANCE_TYPE_NONE);
        $metadata->setPrimaryTable(['name' => 'search_term_boosters']);
        $metadata->setChangeTrackingPolicy(ClassMetadataInfo::CHANGETRACKING_NOTIFY);
        $metadata->mapField([
            'fieldName'  => 'object_type',
            'type'       => 'string',
            'length'     => 100,
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => false,
            'columnName' => 'object_type',
            'id'         => true,
        ]);
        $metadata->mapField([
            'fieldName'  => 'object_id',
            'type'       => 'integer',
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => false,
            'columnName' => 'object_id',
            'id'         => true,
        ]);
        $metadata->mapField([
            'fieldName'  => 'boosted_method',
            'type'       => 'boolean',
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => false,
            'columnName' => 'is_user',
        ]);
        $metadata->mapField([
            'fieldName'  => 'boosted_terms',
            'type'       => 'string',
            'length'     => 255,
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => false,
            'columnName' => 'boosted_terms',
        ]);
    }
}
