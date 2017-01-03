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
use Application\DeskPRO\Entity\Usergroup;
use Application\DeskPRO\EntityRepository\Department as DepartmentRepository;
use DeskPRO\Bundle\AppBundle\Content\AvatarResolver;
use DeskPRO\Bundle\AppBundle\Serializer\Deferred\CallbackDeferredProperty;
use DeskPRO\Bundle\AppBundle\Serializer\Model\Department as DepartmentModel;
use DeskPRO\Bundle\AppBundle\Serializer\Sideload\SideloadSerializationContext;
use Doctrine\ORM\EntityManager;
use JMS\Serializer\JsonSerializationVisitor;

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
     * @var EntityManager
     */
    protected $em;

    private $mightyUsers = [];

    /**
     * Constructor.
     *
     * @param AvatarResolver $resolver
     * @param EntityManager  $em
     */
    public function __construct(AvatarResolver $resolver, EntityManager $em)
    {
        $this->resolver = $resolver;
        $this->em       = $em;
    }

    /**
     * {@inheritdoc}
     */
    public static function getClassNames()
    {
        return Department::class;
    }

    /**
     * @param JsonSerializationVisitor     $visitor
     * @param Department                   $entity
     * @param array                        $type
     * @param SideloadSerializationContext $context
     *
     * @return mixed
     */
    public function serialize(JsonSerializationVisitor $visitor, $entity, $type, SideloadSerializationContext $context)
    {
        $sideloads = $context->getSideloadStore();
        $sideloads->addCustomSideload(
            'agents',
            $entity->getId(),
            new CallbackDeferredProperty([$this, 'getAgents'], [$entity])
        );

        return parent::serialize($visitor, $entity, $type, $context);
    }

    public function getAgents(Department $department)
    {
        if (!$this->mightyUsers) {
            $mightyAgentGroups = $this->em
                ->getRepository(Usergroup::class)
                ->findBy(['sys_name' => [Usergroup::AGENT_ALL_PERM, Usergroup::AGENT_ALL_SAFE_PERM]]);
            foreach ($mightyAgentGroups as $agentGroup) {
                foreach ($agentGroup->getPeople() as $agent) {
                    $this->mightyUsers[] = $agent->getId();
                }
            }
        }

        /** @var DepartmentRepository $departmentRepository */
        $departmentRepository = $this->em->getRepository(Department::class);
        $data                 = $departmentRepository->getPermissionsInfo($department);

        $ids = [];
        foreach ($data['agentgroups'] as $usergroup) {
            if ($usergroup['perm_name'] === 'full') {
                $ids[] = $usergroup['usergroup_id'];
            }
        }

        if ($ids) {
            $usergroups = implode(',', $ids);
            $sql        = "SELECT DISTINCT(person_id) FROM person2usergroups WHERE usergroup_id IN ({$usergroups})";
            $personIds  = $this->em->getConnection()->fetchAll($sql);
            $personIds  = array_column($personIds, 'person_id');
        } else {
            $personIds = [];
        }

        foreach ($personIds as &$id) {
            $id = (int) $id;
        }

        foreach ($data['agents'] as $agent) {
            if ($agent['perm_name'] === 'full') {
                $personIds[] = (int) $agent['agent_id'];
            }
        }

        return array_unique(array_merge($this->mightyUsers, $personIds), SORT_NUMERIC);
    }

    /**
     * {@inheritdoc}
     *
     * @param Department $entity
     */
    protected function createModel($entity, SideloadSerializationContext $context)
    {
        $avatar = $this->resolver->getAvatarModel($entity);

        return new DepartmentModel($entity, $avatar);
    }
}
