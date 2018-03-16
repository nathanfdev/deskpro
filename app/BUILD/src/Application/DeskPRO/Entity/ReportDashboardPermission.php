<?php

/*
 * Deskpro (r) has been developed by Deskpro Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2018, Deskpro Ltd.
 *
 * The license agreement under which this software is released
 * can be found at https://www.deskpro.com/eula/
 *
 * By using this software, you acknowledge having read the license
 * and agree to be bound thereby.
 *
 * Please note that Deskpro is not free software. We release the full
 * source code for our software because we trust our users to pay us for
 * the huge investment in time and energy that has gone into both creating
 * this software and supporting our customers. By providing the source code
 * we preserve our customers' ability to modify, audit and learn from our
 * work. We have been developing Deskpro since 2001, please help us make it
 * another decade.
 *
 * Like the work you see? Think you could make it better? We are always
 * looking for great developers to join us: http://www.deskpro.com/jobs/
 *
 * ~ Thanks, Everyone at Team Deskpro
 */

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\Entity;

use Application\DeskPRO\Domain\DomainObject;
use DeskPRO\Bundle\AppBundle\Validator\Constraints as AppAssert;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use JMS\Serializer\Annotation as JMS;

/**
 * Stores who has access to departments.
 *
 * @JMS\ExclusionPolicy("ALL")
 * @AppAssert\Reports\DashboardPermission()
 */
class ReportDashboardPermission extends DomainObject
{
    /**
     * Name of the "full access" permission
     * (for now we can track only view and edit, so this cannot be that
     * agent can edit, but cant view :) ).
     */
    const FULL = 'full';

    /**
     * Name of the "view" permission.
     */
    const VIEW = 'view';

    /**
     * @var int
     */
    protected $id;

    /**
     * @JMS\Expose()
     * @JMS\Type("entity<Application\DeskPRO\Entity\Person>")
     *
     * @AppAssert\Person\PersonType(type="agent")
     *
     * @var \Application\DeskPRO\Entity\Person
     */
    protected $person = null;

    /**
     * @JMS\Expose()
     * @JMS\Type("entity<Application\DeskPRO\Entity\AgentTeam>")
     *
     * @var \Application\DeskPRO\Entity\AgentTeam
     */
    protected $team = null;

    /**
     * @JMS\Expose()
     * @JMS\Type("entity<Application\DeskPRO\Entity\Department>")
     *
     * @var \Application\DeskPRO\Entity\Department
     */
    protected $department = null;

    /**
     * @var \Application\DeskPRO\Entity\ReportDashboard
     */
    protected $dashboard = null;

    /**
     * The name of the permission.
     *
     * @JMS\Expose()
     * @JMS\Type("string")
     *
     * @var string
     */
    protected $name = null;

    /**
     * @JMS\Expose()
     * @JMS\Type("boolean")
     *
     * @var bool
     */
    protected $viewAll = false;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param ReportDashboard $dashboard
     *
     * @return $this
     */
    public function setDashboard(ReportDashboard $dashboard)
    {
        $this->dashboard = $dashboard;

        return $this;
    }

    /**
     * @param Person $person
     *
     * @return $this
     */
    public function setPerson(Person $person = null)
    {
        if ($person) {
            if ($person->isAgent()) {
                $this->setModelField('person', $person);
            } else {
                //possibly we gonna throw an Exception here, cause it's wrong trying to add just a person here
            }
        }

        return $this;
    }

    /**
     * @return Person
     */
    public function getPerson()
    {
        return $this->person;
    }

    /**
     * @param AgentTeam $team
     *
     * @return $this
     */
    public function setTeam(AgentTeam $team = null)
    {
        $this->team = $team;

        return $this;
    }

    /**
     * @return AgentTeam
     */
    public function getTeam()
    {
        return $this->team;
    }

    /**
     * @param Department $department
     *
     * @return $this
     */
    public function setDepartment(Department $department = null)
    {
        $this->department = $department;

        return $this;
    }

    /**
     * @return Department
     */
    public function getDepartment()
    {
        return $this->department;
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
     * A name that identifies this permission (eg could be used as an map key).
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
        return $this->person;
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

    /**
     * @return bool
     */
    public function isViewAll()
    {
        return $this->viewAll;
    }

    /**
     * @param bool $viewAll
     *
     * @return $this
     */
    public function setViewAll($viewAll)
    {
        $this->setModelField('viewAll', $viewAll);

        return $this;
    }

    /**
     * @return bool
     */
    public function isGlobalPrivilege()
    {
        return !$this->person && $this->team && $this->department;
    }

    //###########################################################################
    // Doctrine Metadata
    //###########################################################################

    public static function loadMetadata(ClassMetadata $metadata)
    {
        $metadata->setInheritanceType(ClassMetadataInfo::INHERITANCE_TYPE_NONE);
        $metadata->setPrimaryTable(['name' => 'report_dashboard_permission']);
        $metadata->setChangeTrackingPolicy(ClassMetadataInfo::CHANGETRACKING_DEFERRED_IMPLICIT);
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
            'fieldName'  => 'name',
            'type'       => 'string',
            'length'     => 50,
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => false,
            'columnName' => 'name',
        ]);
        $metadata->mapField([
            'fieldName'  => 'viewAll',
            'columnName' => 'view_all',
            'type'       => 'boolean',
            'nullable'   => false,
        ]);

        $metadata->setIdGeneratorType(ClassMetadataInfo::GENERATOR_TYPE_IDENTITY);
        $metadata->mapManyToOne([
            'fieldName'    => 'dashboard',
            'targetEntity' => ReportDashboard::class,
            'mappedBy'     => null,
            'inversedBy'   => null,
            'joinColumns'  => [
                0 => [
                    'name'                 => 'dashboard_id',
                    'referencedColumnName' => 'id',
                    'nullable'             => false,
                    'onDelete'             => 'cascade',
                    'columnDefinition'     => null,
                ],
            ],
        ]);
        $metadata->mapManyToOne([
            'fieldName'    => 'person',
            'targetEntity' => Person::class,
            'mappedBy'     => null,
            'inversedBy'   => 'report_dashboard_permissions',
            'joinColumns'  => [
                0 => [
                    'name'                 => 'person_id',
                    'referencedColumnName' => 'id',
                    'nullable'             => true,
                    'onDelete'             => 'cascade',
                ],
            ],
        ]);

        $metadata->mapManyToOne([
            'fieldName'    => 'team',
            'targetEntity' => AgentTeam::class,
            'mappedBy'     => null,
            'inversedBy'   => 'report_dashboard_permissions',
            'joinColumns'  => [
                0 => [
                    'name'                 => 'team_id',
                    'referencedColumnName' => 'id',
                    'nullable'             => true,
                    'onDelete'             => 'cascade',
                ],
            ],
        ]);

        $metadata->mapManyToOne([
            'fieldName'    => 'department',
            'targetEntity' => Department::class,
            'mappedBy'     => null,
            'inversedBy'   => 'report_dashboard_permissions',
            'joinColumns'  => [
                0 => [
                    'name'                 => 'department_id',
                    'referencedColumnName' => 'id',
                    'nullable'             => true,
                    'onDelete'             => 'cascade',
                ],
            ],
        ]);
    }
}
