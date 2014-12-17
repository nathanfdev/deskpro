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
 * Stores who has access to departments
 *
 */
class ReportDashboardPermission extends \Application\DeskPRO\Domain\DomainObject
{
    /**
     * Name of the "full access" permission
     * (for now we can track only view and edit, so this cannot be that
     * agent can edit, but cant view :) )
     */
    const FULL = 'full';

    /**
     * Name of the "view" permission
     */
    const VIEW = 'view';

    /**
     * @var int
     */
    protected $id;

    /**
     * @var \Application\DeskPRO\Entity\Person
     */
    protected $agent = null;

    /** @var \Application\DeskPRO\Entity\ReportDashboard */
    protected $dashboard = null;

    /** @var integer */
    protected $dashboard_id;

    /** @var integer */
    protected $person_id;

    /**
     * The name of the permission
     *
     * @var string
     */
    protected $name = null;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    public function setDashboard(ReportDashboard $dashboard)
    {
        $this->dashboard = $dashboard;
    }

    public function setPerson(Person $p)
    {
        if( $p->getIsAgent() ) {
            $this->setModelField('agent', $p);
        } else {
            //possibly we gonna throw an Exception here, cause it's wrong trying to add just a person here
        }

        return $this;
    }

    /**
     * @param string $name
     *
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }



    /**
     * A name that identifies this permission (eg could be used as an map key)
     *
     * @return string
     */
    public function getPermissionSysId()
    {
        $x = sprintf('d%dp%d%s.1', $this->dashboard->getId(), $this->agent->getId(), $this->name);

        return $x;
    }

    /**
     * @return Person
     */
    public function getAgent()
    {
        return $this->agent;
    }

    /**
     * @return ReportDashboard
     */
    public function getDashboard()
    {
        return $this->dashboard;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    ############################################################################
    # Doctrine Metadata
    ############################################################################

    public static function loadMetadata(ClassMetadata $metadata)
    {
        $metadata->setInheritanceType(ClassMetadataInfo::INHERITANCE_TYPE_NONE);
        $metadata->customRepositoryClassName = 'Application\DeskPRO\EntityRepository\DepartmentPermission';
        $metadata->setPrimaryTable(array( 'name' => 'report_dashboard_permission', ));
        $metadata->setChangeTrackingPolicy(ClassMetadataInfo::CHANGETRACKING_NOTIFY);
        $metadata->mapField(array('fieldName'  => 'id',
                                  'type'       => 'integer',
                                  'precision'  => 0,
                                  'scale'      => 0,
                                  'nullable'   => false,
                                  'columnName' => 'id',
                                  'id'         => true,
            ));
        $metadata->mapField(array('fieldName'  => 'name',
                                  'type'       => 'string',
                                  'length'     => 50,
                                  'precision'  => 0,
                                  'scale'      => 0,
                                  'nullable'   => false,
                                  'columnName' => 'name',
            ));

        $metadata->mapField(array('fieldName'  => 'dashboard_id',
                                  'type'       => 'integer',
                                  'precision'  => 0,
                                  'scale'      => 0,
                                  'nullable'   => false,
                                  'columnName' => 'dashboard_id',
                                  'id'         => false,
            ));
        $metadata->mapField(array('fieldName'  => 'person_id',
                                  'type'       => 'integer',
                                  'precision'  => 0,
                                  'scale'      => 0,
                                  'nullable'   => false,
                                  'columnName' => 'person_id',
                                  'id'         => false,
            ));
        $metadata->setIdGeneratorType(ClassMetadataInfo::GENERATOR_TYPE_IDENTITY);
        $metadata->mapManyToOne(array(
            'fieldName'    => 'department',
            'targetEntity' => 'Application\\DeskPRO\\Entity\\ReportDashboard',
            'mappedBy'     => null,
            'inversedBy'   => null,
            'joinColumns'  => array(
                0 => array(
                    'name'                 => 'dashboard_id',
                    'referencedColumnName' => 'id',
                    'nullable'             => true,
                    'onDelete'             => 'cascade',
                    'columnDefinition'     => null,
                ),
            ),
        ));
        $metadata->mapManyToOne(array(
            'fieldName'    => 'agent',
            'targetEntity' => 'Application\\DeskPRO\\Entity\\Person',
            'mappedBy'     => null,
            'inversedBy'   => 'report_dashboard_permissions',
            'joinColumns'  => array(
                0 => array(
                    'name'                 => 'person_id',
                    'referencedColumnName' => 'id',
                    'nullable'             => true,
                    'onDelete'             => 'cascade'
                ),
            ),
        ));
    }
}
