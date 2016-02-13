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

namespace DeskPRO\Bundle\AppBundle\ActionEngine\Actions\Feedback;

use Application\DeskPRO\Entity\Feedback;
use DeskPRO\Bundle\AppBundle\ActionEngine\ActionInterface;
use DeskPRO\Bundle\AppBundle\ActionEngine\Actions\AbstractAction;
use DeskPRO\Bundle\AppBundle\ActionEngine\ActionWithOptionsInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SetStatusCategoryAction extends AbstractAction implements ActionInterface, ActionWithOptionsInterface
{
    private $statusCategory;

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired('id');
        $resolver->setAllowedValues(
            'id',
            function ($value) {
                return is_int($value) || ctype_digit($value);
            }
        );
    }

    /**
     * Fetch status category (FeedbackStatusCategory).
     */
    public function init()
    {
        $id                   = $this->options['id'];
        $this->statusCategory = $this->em->getRepository('DeskPRO:FeedbackStatusCategory')->find($id);
    }

    /**
     * @param Feedback $feedback
     */
    public function run($feedback)
    {
        $feedback->setStatusCategory($this->statusCategory);
    }

    /** @return array */
    public function getSerialized()
    {
        return [self::SET_STATUS_CATEGORY_ACTION => ['id' => $this->options['id']]];
    }
}
