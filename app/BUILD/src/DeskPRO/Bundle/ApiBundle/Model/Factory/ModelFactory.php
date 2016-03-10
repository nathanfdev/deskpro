<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2016, DeskPRO Ltd.
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

namespace DeskPRO\Bundle\ApiBundle\Model\Factory;

use Application\DeskPRO\Entity\AgentTeam as AgentTeamEntity;
use DeskPRO\Bundle\ApiBundle\Model\AgentTeam;
use DeskPRO\Component\Util\TypeUtils;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class ModelFactory.
 */
class ModelFactory
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * ModelFactory constructor.
     *
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @var array
     */
    protected $methodMap = [
        'person' => [
            'ApiPerson' => [
                'default' => true,
                'factory' => 'api_serializer.api_person_factory',
                'method'  => 'create',
            ],
        ],
        'agent_team' => 'createAgentTeam',

    ];

    /**
     * @param      $entity
     * @param null $concrete
     */
    public function create($entity, $concrete = null)
    {
        $snake = TypeUtils::getSnakeCaseBaseTypeName($entity);
        if (!isset($this->methodMap[$snake])) {
            return $entity;
        }

        if (is_array($this->methodMap[$snake])) {
            return $this->createPolymorph($this->methodMap[$snake], $entity, $concrete);
        }

        return $this->{$this->methodMap[$snake]}($entity);
    }

    /**
     * @param      $entities
     * @param null $concrete
     *
     * @return array
     */
    public function createArray($entities, $concrete = null)
    {
        $wrappers = [];
        foreach ($entities as $entity) {
            $wrappers[] = $this->create($entity, $concrete);
        }

        return $wrappers;
    }

    /**
     * @param $polymorphs
     * @param $concrete
     */
    protected function createPolymorph($polymorphs, $entity, $concrete)
    {
        if (null === $concrete) {
            $concrete = $this->findDefault($polymorphs);
        } else {
            $concrete = isset($polymorphs[$concrete]) ? $polymorphs : $this->findDefault($polymorphs);
        }
        if (isset($concrete['factory'])) {
            if (!$this->container->has($concrete['factory'])) {
                throw new \UnexpectedValueException(sprintf('Service [ %s ] is not registered in DI container', $concrete['factory']));
            }
            $concrete_factory = $this->container->get($concrete['factory']);
        } else {
            $concrete_factory = $this;
        }

        if (!isset($concrete['method'])) {
            throw  new \UnexpectedValueException('Factory definition should contain "method" key');
        }

        return $concrete_factory->{$concrete['method']}($entity);
    }

    /**
     * @param $polymorphs
     *
     * @return mixed
     */
    protected function findDefault($polymorphs)
    {
        foreach ($polymorphs as $polymorph) {
            if (isset($polymorph['default']) && $polymorph['default'] === true) {
                return $polymorph;
            }
        }

        return array_pop($polymorphs);
    }

    /**
     * @param AgentTeamEntity $entity
     *
     * @return $this
     */
    protected function createAgentTeam(AgentTeamEntity $entity)
    {
        $avatar_resolver = $this->container->get('avatar_resolver');

        $agent_team = new AgentTeam($entity);

        return $agent_team->setAvatar($avatar_resolver->getAvatarModel($entity));
    }
}
