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

namespace DeskPRO\Bundle\AppBundle\ActionEngine\Services;

use DeskPRO\Bundle\AppBundle\ActionEngine\ActionCollection\ActionCollection;
use DeskPRO\Bundle\AppBundle\ActionEngine\Actions\ActionInterface;
use DeskPRO\Bundle\AppBundle\ActionEngine\Applicators\AbstractActionApplicator;
use DeskPRO\Bundle\AppBundle\ActionEngine\Applicators\ActionInitializationInterface;
use DeskPRO\Bundle\AppBundle\ActionEngine\Utils\ActionTransformer;
use DeskPRO\Bundle\AppBundle\ActionEngine\Utils\ActionTypeCodes;
use Doctrine\ORM\EntityManager;

abstract class AbstractApplicatorService implements ApplicatorServiceInterface
{
    protected $class;
    protected $namespace;
    /** @var EntityManager */
    protected $em;
    /** @var ActionTransformer */
    protected $transformer;

    public function __construct(EntityManager $em)
    {
        $this->em          = $em;
        $this->transformer = new ActionTransformer();
    }

    public function apply(array $ids, array $params)
    {
        $objects          = $this->getEntities($ids);
        $actionCollection = $this->getActionCollection($params);
        foreach ($objects as $object) {
            foreach ($actionCollection->getApplicators() as $applicator) {
                $applicator->apply($object);
            }
        }
    }

    /**
     * @param string $actionName
     *
     * @return AbstractActionApplicator
     */
    protected function createApplicator($actionName)
    {
        $applicatorClass = $this->getApplicatorClass($actionName);

        return new $applicatorClass($this->em);
    }

    /**
     * Fetch entities for mass action apply.
     *
     * @param array $ids
     *
     * @return array
     */
    protected function getEntities(array $ids)
    {
        $qb = $this->em->createQueryBuilder();
        $qb
            ->select('entity')
            ->from($this->class, 'entity')
            ->where('entity.id IN (:ids)')
            ->setParameter('ids', $ids);

        return $qb->getQuery()->getResult();
    }

    /**
     * Transform array of actions parameters into ActionCollection.
     *
     * @param array $params
     *
     * @return ActionCollection
     */
    private function getActionCollection(array $params)
    {
        $actionCollection = new ActionCollection();
        $actionCollection->prepare($this->namespace, $params);
        foreach ($actionCollection->getActions() as $action) {
            $applicator = $this->createApplicator($action->getName());
            $this->initApplicator($action, $applicator);
            $actionCollection->addApplicator($applicator);
        }

        return $actionCollection;
    }

    /**
     * @param ActionInterface $action
     *
     * @return array
     */
    private function getOptions(ActionInterface $action)
    {
        return $action->serialize() ?: [];
    }

    /**
     * @param string $actionName
     *
     * @return string
     */
    private function getApplicatorClass($actionName)
    {
        return ActionTypeCodes::getActionApplicatorClassForActionName($this->namespace, $actionName);
    }

    /**
     * @param ActionInterface          $action
     * @param AbstractActionApplicator $applicator
     */
    private function initApplicator(ActionInterface $action, AbstractActionApplicator $applicator)
    {
        $options = $this->getOptions($action);
        $applicator->setOptions($options);

        if ($applicator instanceof ActionInitializationInterface) {
            $applicator->init();
        }
    }
}
