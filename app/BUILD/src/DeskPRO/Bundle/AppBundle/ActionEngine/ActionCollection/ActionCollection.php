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
namespace DeskPRO\Bundle\AppBundle\ActionEngine\ActionCollection;

use DeskPRO\Bundle\AppBundle\ActionEngine\Actions\AbstractAction;
use DeskPRO\Bundle\AppBundle\ActionEngine\Actions\ActionInterface;
use DeskPRO\Bundle\AppBundle\ActionEngine\Applicators\ActionApplicatorInterface;
use DeskPRO\Bundle\AppBundle\ActionEngine\Utils\ActionTypeCodes;
use Doctrine\Common\Collections\ArrayCollection;

class ActionCollection
{
    /** @var ArrayCollection|ActionInterface[] */
    private $actions;

    /** @var ArrayCollection|ActionApplicatorInterface[] */
    private $applicators;

    public function __construct()
    {
        $this->actions     = new ArrayCollection();
        $this->applicators = new ArrayCollection();
    }

    /**
     * @return ActionInterface[]
     */
    public function getActions()
    {
        return $this->actions;
    }

    /**
     * @param ActionApplicatorInterface $applicator
     */
    public function addApplicator(ActionApplicatorInterface $applicator)
    {
        $this->applicators->add($applicator);
    }

    /**
     * @return ActionApplicatorInterface[]
     */
    public function getApplicators()
    {
        return $this->applicators;
    }

    /**
     * @param string $namespace
     * @param array  $actions
     *
     * @return ActionCollection
     */
    public function prepare($namespace, array $actions)
    {
        foreach ($actions as $name => $options) {
            // Set of actions without options (like delete, mark as spam, approve and so on)
            if ($name === AbstractAction::SET_OF_ACTIONS) {
                foreach ($options as $type) {
                    $this->resolveAction($namespace, $type);
                }
            } else {
                $this->resolveAction($namespace, $name, $options);
            }
        }

        return $this;
    }

    private function resolveAction($namespace, $name, $options = null)
    {
        $actionClass = ActionTypeCodes::getActionClass($namespace, $name);
        if ($options) {
            $this->addAction(new $actionClass([$name => $options]));
        } else {
            $this->addAction(new $actionClass());
        }

        return $this;
    }

    private function addAction(ActionInterface $action)
    {
        $this->actions->add($action);

        return $this;
    }
}
