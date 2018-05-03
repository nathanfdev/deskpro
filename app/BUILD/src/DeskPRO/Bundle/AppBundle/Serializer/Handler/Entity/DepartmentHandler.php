<?php

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
