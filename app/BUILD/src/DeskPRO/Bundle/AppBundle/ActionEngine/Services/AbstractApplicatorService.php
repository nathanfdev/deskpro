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
use DeskPRO\Bundle\AppBundle\ActionEngine\Utils\ActionTransformer;
use DeskPRO\Bundle\AppBundle\ActionEngine\Utils\ActionTypeCodes;
use Doctrine\ORM\EntityManager;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ContainerInterface;

abstract class AbstractApplicatorService implements ApplicatorServiceInterface
{
    private $container;
    /** @var EntityManager */
    protected $em;
    protected $actionCollection;
    protected $class;
    protected $namespace;
    protected $transformer;

    public function __construct(Container $container)
    {
        $this->container        = $container;
        $this->em               = $this->container->get('doctrine.orm.default_entity_manager');
        $this->transformer      = new ActionTransformer();
        $this->actionCollection = new ActionCollection();
    }

    /**
     * @param array $ids
     * @param array $actions
     */
    public function apply(array $ids, array $actions)
    {
        $entities = $this->getEntities($this->class, $ids);
        print_r($actions);
        $this->actionCollection->prepare($actions);
        /** @var ActionInterface $action */
        foreach ($this->actionCollection->getActions() as $action) {
            $serialized = $action->serialize();
            print_r($serialized);
            $options = array_key_exists('options', $serialized) && $serialized['options'] ?
                $serialized['options'] : [];
            print_r($options);
            $type = ActionTypeCodes::getActionTypeCode($action);
            echo $type;
            $applicator = $this->container->get(
                'action_engine.'.strtolower($this->namespace).'.'.$type,
                ContainerInterface::NULL_ON_INVALID_REFERENCE
            );
            if (null === $applicator) {
                $applicator = $this->transformer->actionToApplicator($this->em, $this->namespace, $type);
            }
            $applicator
                ->setOptions($options)
                ->apply($entities);
        }
        $this->em->flush();
    }

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
}
