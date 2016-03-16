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
use DeskPRO\Bundle\AppBundle\ActionEngine\Actions\Common\AddLabelsAction;
use DeskPRO\Bundle\AppBundle\ActionEngine\Actions\Common\ApproveAction;
use DeskPRO\Bundle\AppBundle\ActionEngine\Actions\Common\DeleteAction;
use DeskPRO\Bundle\AppBundle\ActionEngine\Actions\Common\RemoveLabelsAction;
use DeskPRO\Bundle\AppBundle\ActionEngine\Actions\Feedback\SetCategoryAction;
use DeskPRO\Bundle\AppBundle\ActionEngine\Actions\Feedback\SetHiddenStatusAction;
use DeskPRO\Bundle\AppBundle\ActionEngine\Actions\Feedback\SetStatusCategoryAction;
use DeskPRO\Bundle\AppBundle\ActionEngine\Actions\Feedback\SetTypeAction;
use DeskPRO\Bundle\AppBundle\ActionEngine\Actions\Task\AssignAction;
use DeskPRO\Bundle\AppBundle\ActionEngine\Actions\Task\SetDueDateAction;
use DeskPRO\Bundle\AppBundle\ActionEngine\Actions\Task\SetProjectAction;
use DeskPRO\Bundle\AppBundle\ActionEngine\Actions\Task\SetStatusAction;
use DeskPRO\Bundle\AppBundle\ActionEngine\Utils\ActionTransformer;
use Doctrine\Common\Collections\ArrayCollection;

class ActionCollection
{
    /** @var ArrayCollection */
    private $actions;
    private $transformer;

    public function __construct(ActionTransformer $transformer)
    {
        $this->actions     = new ArrayCollection();
        $this->transformer = $transformer;
    }

    public function addAction(ActionInterface $action)
    {
        $this->actions->add($action);

        return $this;
    }

    public function getActions()
    {
        return $this->actions;
    }

    /**
     * @param string $namespace
     * @param mixed  $entities
     * @param array  $actions
     */
    public function apply($namespace, $entities, $actions)
    {
        $this->prepare($actions);
        /** @var ActionInterface $action */
        foreach ($this->actions as $action) {
            $applicator = $this->transformer->actionToApplicator($namespace, $action);
            $applicator->apply($entities);
        }
    }

    /**
     * @param array $actions
     */
    private function prepare(array $actions)
    {
        foreach ($actions as $name => $options) {
            switch ($name) {
                case AbstractAction::ASSIGN_ACTION:
                    $this->addAction(new AssignAction(['assign' => $options]));
                    break;
                case AbstractAction::SET_STATUS_CATEGORY_ACTION:
                    $this->addAction(new SetStatusCategoryAction(['id' => $options]));
                    break;
                case AbstractAction::SET_STATUS_ACTION:
                    $this->addAction(new SetStatusAction(['status' => (int) $options]));
                    break;
                case AbstractAction::SET_DUE_DATE_ACTION:
                    $this->addAction(new SetDueDateAction(['date' => $options]));
                    break;
                case AbstractAction::SET_HIDDEN_STATUS_ACTION:
                    $this->addAction(new SetHiddenStatusAction(['input' => $options]));
                    break;
                case AbstractAction::SET_TYPE_ACTION:
                    $this->addAction(new SetTypeAction(['id' => $options]));
                    break;
                case AbstractAction::SET_CATEGORY_ACTION:
                    $this->addAction(new SetCategoryAction(['input' => $options]));
                    break;
                case AbstractAction::SET_PROJECT_ACTION:
                    $this->addAction(new SetProjectAction(['id' => $options]));
                    break;
                case AbstractAction::ADD_LABELS_ACTION:
                    $this->addAction(new AddLabelsAction(['labels' => $options]));
                    break;
                case AbstractAction::REMOVE_LABELS_ACTION:
                    $this->addAction(new RemoveLabelsAction(['labels' => $options]));
                    break;
                case AbstractAction::APPROVE_ACTION:
                    $this->addAction(new ApproveAction());
                    break;
                case AbstractAction::DELETE_ACTION:
                    $this->addAction(new DeleteAction());
                    break;
            }
        }
    }
}
