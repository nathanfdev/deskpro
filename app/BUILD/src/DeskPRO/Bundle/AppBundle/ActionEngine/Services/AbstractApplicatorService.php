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

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\AppBundle\ActionEngine\Services;

use DeskPRO\Bundle\AppBundle\ActionEngine\ActionCollection\ActionCollection;
use DeskPRO\Bundle\AppBundle\ActionEngine\Actions\ActionInterface;
use DeskPRO\Bundle\AppBundle\ActionEngine\Applicators\AbstractActionApplicator;
use DeskPRO\Bundle\AppBundle\ActionEngine\Utils\ActionTransformer;
use DeskPRO\Bundle\AppBundle\ActionEngine\Utils\ActionTypeCodes;
use Doctrine\ORM\EntityManager;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ContainerInterface;

abstract class AbstractApplicatorService implements ApplicatorServiceInterface
{
    protected $class;
    protected $namespace;
    /** @var EntityManager */
    protected $em;
    /** @var ActionCollection */
    protected $actionCollection;
    /** @var ActionTransformer */
    protected $transformer;

    private $container;

    public function __construct(Container $container)
    {
        // Applicator Service need Container for inject additional services into action applicators (if necessary)
        $this->container        = $container;
        $this->em               = $this->container->get('doctrine.orm.default_entity_manager');
        $this->transformer      = $this->container->get('action_engine.action_transformer');
        $this->actionCollection = $this->container->get('action_engine.action_collection');
    }

    /**
     * @param array $ids
     * @param array $actions
     */
    public function apply(array $ids, array $actions)
    {
        $entities = $this->getEntities($this->class, $ids);
        // Transform array of actions into ActionInterface collection
        $this->actionCollection->prepare($this->namespace, $actions);

        /** @var ActionInterface $action */
        foreach ($this->actionCollection->getActions() as $action) {
            $applicator = $this->getApplicator($action);
            $options    = $this->getOptions($action);
            $applicator
                ->setOptions($options)
                ->apply($entities);
        }

        $this->em->flush();
    }

    /**
     * Fetch entities for mass action apply.
     *
     * @param string $class
     * @param array  $ids
     *
     * @return array
     */
    private function getEntities($class, array $ids)
    {
        $qb = $this->em->createQueryBuilder();
        $qb
            ->select('entity')
            ->from($class, 'entity')
            ->where('entity.id IN (:ids)')
            ->setParameter('ids', $ids);

        return $qb->getQuery()->getResult();
    }

    /**
     * @param ActionInterface $action
     *
     * @return array
     */
    private function getOptions(ActionInterface $action)
    {
        $serialized = $action->serialize();
        $options    = array_key_exists('options', $serialized) && $serialized['options'] ?
            $serialized['options'] : [];

        return $options;
    }

    /**
     * @param ActionInterface $action
     *
     * @return AbstractActionApplicator
     */
    private function getApplicator(ActionInterface $action)
    {
        $type = ActionTypeCodes::getActionTypeCode($action);

        // If applicator need some injected services (except Entity Manager), it must be configured in container
        $applicator = $this->container->get(
            'action_engine.'.strtolower($this->namespace).'.'.$type,
            ContainerInterface::NULL_ON_INVALID_REFERENCE
        );
        // Otherwise we try to get applicator by Action Transformer
        if (null === $applicator) {
            $applicator = $this->transformer->actionToApplicator($this->em, $this->namespace, $type);
        }

        return $applicator;
    }
}
