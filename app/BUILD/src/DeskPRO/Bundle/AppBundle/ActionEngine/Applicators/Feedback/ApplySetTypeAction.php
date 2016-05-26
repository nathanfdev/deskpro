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

namespace DeskPRO\Bundle\AppBundle\ActionEngine\Applicators\Feedback;

use Application\DeskPRO\Entity\Feedback;
use Application\DeskPRO\Entity\FeedbackCategory;
use DeskPRO\Bundle\AppBundle\ActionEngine\Applicators\AbstractActionApplicator;
use DeskPRO\Bundle\AppBundle\ActionEngine\Applicators\ActionInitializationInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class ApplySetTypeAction extends AbstractActionApplicator implements ActionInitializationInterface
{
    private $type;

    public function init()
    {
        $this->type = $this->em->getRepository(FeedbackCategory::class)->find($this->options['set_type']);
        if (null === $this->type) {
            throw new BadRequestHttpException('Feedback type with ID='.$this->options['set_type']." doesn't exists");
        }
    }

    /**
     * @param Feedback $feedback
     */
    public function apply($feedback)
    {
        $feedback->setCategory($this->type);
    }
}
