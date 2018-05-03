<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\Entity;

use Doctrine\ORM\Mapping\ClassMetadata;
use Orb\Doctrine\ORM\Mapping\Builder\ClassMetadataBuilder;

/**
 * Storage for the "newer" session data.
 *
 * @property string $sess_id
 * @property string $sess_data
 * @property int $sess_time
 */
class SessData extends \Application\DeskPRO\Domain\DomainObject
{
    protected $sess_id;

    protected $sess_data;

    protected $sess_time;

    protected $visitor_id;

    protected $person_id;

    //###########################################################################
    // Doctrine Metadata
    //###########################################################################

    public static function loadMetadata(ClassMetadata $metadata)
    {
        $builder = new ClassMetadataBuilder($metadata);

        $builder->setTable('sess_data');

        $builder->createField('sess_id', 'string', [
                'nullable' => false,
            ]
        )->isPrimaryKey()->build();
        $builder->addField('sess_time', 'integer', [
                'unsigned' => true,
                'nullable' => false,
            ]
        );
        $builder->addField('visitor_id', 'string', [
                'nullable' => true,
            ]
        );
        $builder->addField('person_id', 'integer', [
                'nullable' => true,
            ]
        );
        $builder->addField('sess_data', 'text', [
                'nullable' => false,
            ]
        );
    }
}
