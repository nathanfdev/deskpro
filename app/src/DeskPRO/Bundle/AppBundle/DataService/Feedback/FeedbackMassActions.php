<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2015, DeskPRO Ltd.
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
namespace DeskPRO\Bundle\AppBundle\DataService\Feedback;

use Application\DeskPRO\Entity\Feedback;
use DeskPRO\Bundle\AppBundle\ActionEngine\Actions\AbstractAction;
use DeskPRO\Bundle\AppBundle\ActionEngine\Actions\Feedback\AddLabelsAction;
use DeskPRO\Bundle\AppBundle\ActionEngine\Actions\Feedback\ApproveAction;
use DeskPRO\Bundle\AppBundle\ActionEngine\Actions\Feedback\DeleteAction;
use DeskPRO\Bundle\AppBundle\ActionEngine\Actions\Feedback\RemoveLabelsAction;
use DeskPRO\Bundle\AppBundle\ActionEngine\Actions\Feedback\SetCategoryAction;
use DeskPRO\Bundle\AppBundle\ActionEngine\Actions\Feedback\SetStatusCategoryAction;
use DeskPRO\Bundle\AppBundle\ActionEngine\Actions\Feedback\SetTypeAction;
use DeskPRO\Bundle\AppBundle\Data\MassActions\AbstractMassActionsPreprocessor;
use DeskPRO\Bundle\AppBundle\Data\MassActions\MassActionsPreprocessorInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FeedbackMassActions extends AbstractMassActionsPreprocessor implements MassActionsPreprocessorInterface
{
    protected static $entity = Feedback::class;

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefined(
            ['status_category', 'type', 'category', 'addLabels', 'removeLabels', 'approve', 'delete']
        );
    }

    /**
     * @return \DeskPRO\Bundle\AppBundle\ActionEngine\ActionCollection
     */
    public function prepareActions()
    {
        foreach ($this->params['actions'] as $name => $options) {
            switch ($name) {
                case AbstractAction::SET_STATUS_CATEGORY_ACTION:
                    $this->actions->addAction(new SetStatusCategoryAction($this->em, $options));
                    break;
                case AbstractAction::SET_TYPE_ACTION:
                    $this->actions->addAction(new SetTypeAction($this->em, $options));
                    break;
                case AbstractAction::SET_CATEGORY_ACTION:
                    $this->actions->addAction(new SetCategoryAction($this->em, $options));
                    break;
                case AbstractAction::ADD_LABELS_ACTION:
                    $this->actions->addAction(new AddLabelsAction($this->em, $options));
                    break;
                case AbstractAction::REMOVE_LABELS_ACTION:
                    $this->actions->addAction(new RemoveLabelsAction($this->em, $options));
                    break;
                case AbstractAction::APPROVE_ACTION:
                    $this->actions->addAction(new ApproveAction($this->em));
                    break;
                case AbstractAction::DELETE_ACTION:
                    $this->actions->addAction(new DeleteAction());
                    break;
            }
        }

        return $this->actions;
    }
}
