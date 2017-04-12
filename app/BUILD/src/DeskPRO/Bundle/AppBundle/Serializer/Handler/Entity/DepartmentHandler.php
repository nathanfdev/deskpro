<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2017, DeskPRO Ltd.
 *
 * The license agreement under which this software is released
 * can be found at https://www.deskpro.com/eula/
 *
 * By using this software, you acknowledge having read the license
 * and agree to be bound thereby.
 *
 * Please note that DeskPRO is not free software. We release the full
 * source code for our software because we trust our users to pay us for
 * the huge investment in time and energy that has gone into both creating
 * this software and supporting our customers. By providing the source code
 * we preserve our customers' ability to modify, audit and learn from our
 * work. We have been developing DeskPRO since 2001, please help us make it
 * another decade.
 *
 * Like the work you see? Think you could make it better? We are always
 * looking for great developers to join us: http://www.deskpro.com/jobs/
 *
 * ~ Thanks, Everyone at Team DeskPRO
 */

namespace DeskPRO\Bundle\AppBundle\Serializer\Handler\Entity;

use Application\DeskPRO\Entity\Department;
use Application\DeskPRO\Entity\Person;
use DeskPRO\Bundle\AppBundle\Content\AvatarResolver;
use DeskPRO\Bundle\AppBundle\DataService\DepartmentDataService;
use DeskPRO\Bundle\AppBundle\Serializer\Deferred\CallbackDeferredProperty;
use DeskPRO\Bundle\AppBundle\Serializer\Model\Department as DepartmentModel;
use DeskPRO\Bundle\AppBundle\Serializer\Sideload\SideloadSerializationContext;

/**
 * Class TaskProjectHandler.
 */
class DepartmentHandler extends AbstractEntityHandler
{
    /**
     * @var AvatarResolver
     */
    protected $resolver;

    /**
     * @var DepartmentDataService
     */
    protected $departmentsData;

    /**
     * Constructor.
     *
     * @param AvatarResolver        $resolver
     * @param DepartmentDataService $departmentsData
     */
    public function __construct(AvatarResolver $resolver, DepartmentDataService $departmentsData)
    {
        $this->resolver        = $resolver;
        $this->departmentsData = $departmentsData;
    }

    /**
     * {@inheritdoc}
     */
    public static function getClassNames()
    {
        return Department::class;
    }

    /**
     * @internal
     *
     * @param Department $department
     *
     * @return Person[]
     */
    public function getAgents(Department $department)
    {
        return $this->departmentsData->getDepartmentAgents($department);
    }

    /**
     * {@inheritdoc}
     *
     * @param Department $entity
     */
    public function createModel($entity, SideloadSerializationContext $context)
    {
        $avatar = $this->resolver->getAvatarModel($entity);
        $model  = new DepartmentModel($entity, $avatar);

        $sideloads = $context->getSideloadStore();
        $sideloads->addCustomSideload(
            'department_agent_ids',
            $entity->getId(),
            new CallbackDeferredProperty([$this, 'getAgents'], [$entity]),
            $model
        );

        return $model;
    }
}
