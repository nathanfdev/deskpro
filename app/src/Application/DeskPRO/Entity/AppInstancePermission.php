<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/  |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2014, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at https://www.deskpro.com/eula/                            |
|                                                                          |
| By using this software, you acknowledge having read the license          |
| and agree to be bound thereby.                                           |
|                                                                          |
| Please note that DeskPRO is not free software. We release the full       |
| source code for our software because we trust our users to pay us for    |
| the huge investment in time and energy that has gone into both creating  |
| this software and supporting our customers. By providing the source code |
| we preserve our customers' ability to modify, audit and learn from our   |
| work. We have been developing DeskPRO since 2001, please help us make it |
| another decade.                                                          |
|                                                                          |
| Like the work you see? Think you could make it better? We are always     |
| looking for great developers to join us: http://www.deskpro.com/jobs/    |
|                                                                          |
| ~ Thanks, Everyone at Team DeskPRO                                       |
\**************************************************************************/

/**
 * DeskPRO
 *
 * @package DeskPRO
 * @category Entities
 */

namespace Application\DeskPRO\Entity;

use Application\DeskPRO\App;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\ClassMetadataInfo;

/**
 * @property int $id
 * @property bool $is_global
 * @property AppInstance $app_instance
 * @property Usergroup $usergroup
 * @property Person $person
 */
class AppInstancePermission extends \Application\DeskPRO\Domain\DomainObject
{
    /**
     * @var int
     */
    protected $id;

    /**
     * @var AppInstance
     */
    protected $app_instance;

    /**
     * @var Usergroup
     */
    protected $usergroup;

    /**
     * @var Person
     */
    protected $person;

    /**
     * @param Usergroup $v
     */
    public function setUsergroup(Usergroup $v)
    {
        $this->setModelField('usergroup', $v);
        $this->setModelField('person', null);
    }

    /**
     * @param Person $v
     */
    public function setPerson(Person $v)
    {
        $this->setModelField('usergroup', null);
        $this->setModelField('person', $v);
    }

    ############################################################################
    # Doctrine Metadata
    ############################################################################

    public static function loadMetadata(ClassMetadata $metadata)
    {
        $metadata->inheritanceType           = ClassMetadataInfo::INHERITANCE_TYPE_NONE;
        $metadata->changeTrackingPolicy      = ClassMetadataInfo::CHANGETRACKING_NOTIFY;
        $metadata->generatorType             = ClassMetadataInfo::GENERATOR_TYPE_IDENTITY;
        $metadata->setPrimaryTable(array(
            'name' => 'app_instance_permissions'
        ));

        $metadata->mapField(array(
            'columnName' => 'id',
            'fieldName'  => 'id',
            'type'       => 'integer',
            'id'         => true,
            'nullable'   => false,
        ));

        $metadata->mapManyToOne(array(
            'fieldName'    => 'app_instance',
            'targetEntity' => 'Application\\DeskPRO\\Entity\\AppInstance',
            'joinColumns'  => array(array(
                'name'                 => 'app_instance_id',
                'referencedColumnName' => 'id',
                'nullable'             => true,
                'onDelete'             => 'CASCADE'
            ))
        ));

        $metadata->mapManyToOne(array(
            'fieldName'    => 'usergroup',
            'targetEntity' => 'Application\\DeskPRO\\Entity\\Usergroup',
            'joinColumns'  => array(array(
                'name'                 => 'usergroup_id',
                'referencedColumnName' => 'id',
                'nullable'             => true,
                'onDelete'             => 'CASCADE'
            ))
        ));

        $metadata->mapManyToOne(array(
            'fieldName'    => 'person',
            'targetEntity' => 'Application\\DeskPRO\\Entity\\Person',
            'joinColumns'  => array(array(
                'name'                 => 'person_id',
                'referencedColumnName' => 'id',
                'nullable'             => true,
                'onDelete'             => 'CASCADE'
            ))
        ));
    }
}
