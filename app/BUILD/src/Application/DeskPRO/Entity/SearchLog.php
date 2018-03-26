<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\Entity;

use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Orb\Util\Strings;

/**
 * Log of searches on user end.
 *
 * @property Person $person
 * @property string $ip_address
 * @property string $email
 * @property int $id;
 * @property string $name
 * @property string $query
 * @property string $num_results
 * @property \DateTime $date_created
 */
class SearchLog extends \Application\DeskPRO\Domain\DomainObject
{
    /**
     * @var int
     */
    protected $id = null;

    /**
     * @var \Application\DeskPRO\Entity\Person
     */
    protected $person = null;

    /**
     * @var string
     */
    protected $visitor_id = null;

    /**
     * @var string
     */
    protected $ip_address = '';

    /**
     * @var string
     */
    protected $email = null;

    /**
     * @var string
     */
    protected $name = null;

    /**
     * @var string
     */
    protected $query;

    /**
     * @var string
     */
    protected $num_results;

    /**
     * @var \DateTime
     */
    protected $date_created;

    /**
     * @param $query
     * @param $num_results
     *
     * @return SearchLog
     */
    public static function create($query, $num_results)
    {
        $searchlog              = new self();
        $searchlog->query       = $query;
        $searchlog->num_results = $num_results;

        return $searchlog;
    }

    public function __construct()
    {
        $this['date_created'] = new \DateTime();
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Sets the query after trying to normalize it a bit.
     *
     * @param $query
     */
    public function setQuery($query)
    {
        $query = trim($query);
        $query = preg_replace('# {2,}#', ' ', $query);
        $query = Strings::utf8_strtolower($query);
        $query = Strings:: utf8_accents_to_ascii($query);

        $this['query'] = $query;
    }

    //###########################################################################
    // Doctrine Metadata
    //###########################################################################

    public static function loadMetadata(ClassMetadata $metadata)
    {
        $metadata->setInheritanceType(ClassMetadataInfo::INHERITANCE_TYPE_NONE);
        $metadata->customRepositoryClassName = 'Application\DeskPRO\EntityRepository\SearchLog';
        $metadata->setPrimaryTable([
            'name'    => 'searchlog',
            'indexes' => [
                'searchlog_query_idx' => ['columns' => ['query']],
                'num_results_idx'     => ['columns' => ['num_results']],
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
            'fieldName'  => 'ip_address',
            'type'       => 'string',
            'length'     => 30,
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => false,
            'columnName' => 'ip_address',
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
            'fieldName'  => 'email',
            'type'       => 'string',
            'length'     => 255,
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => true,
            'columnName' => 'email',
        ]);
        $metadata->mapField([
            'fieldName'  => 'name',
            'type'       => 'string',
            'length'     => 255,
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => true,
            'columnName' => 'name',
        ]);
        $metadata->mapField([
            'fieldName'  => 'query',
            'type'       => 'string',
            'length'     => 255,
            'nullable'   => false,
            'columnName' => 'query',
        ]);
        $metadata->mapField([
            'fieldName'  => 'num_results',
            'type'       => 'integer',
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => false,
            'columnName' => 'num_results',
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
                    'onDelete'             => 'set null',
                    'columnDefinition'     => null,
                ],
            ],
        ]);
    }
}
