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

use Application\DeskPRO\Entity\CustomDataFeedback;
use DeskPRO\Bundle\AppBundle\Data\MassActions\MassActionsPreprocessorInterface;
use Doctrine\ORM\EntityManager;

class FeedbackMassActions implements MassActionsPreprocessorInterface
{
    private $em;
    private $params;
    private $statusCategory;
    private $category;
    private $customCategory;

    public function __construct(EntityManager $em, array $params)
    {
        $this->em     = $em;
        $this->params = $params;
    }

    public function prepareActions()
    {
        foreach ($this->params['actions'] as $key => $value) {
            switch ($key) {
                case 'status_category':
                    $this->statusCategory = $this->em->getRepository('DeskPRO:FeedbackStatusCategory')->find($value);
                    break;
                case 'category':
                    $this->category = $this->em->getRepository('DeskPRO:FeedbackCategory')->find($value);
                    break;
                case 'custom_category':
                    $this->customCategory = new CustomDataFeedback();
                    $customDef            = $this->em->getRepository('DeskPRO:CustomDefFeedback')
                        ->findOneBy(['title' => 'Category']);
                    $this->customCategory->setField($customDef);
                    break;
            }
        }
    }

    public function selectEntities()
    {
        $qb = $this->em->createQueryBuilder();
        $qb
            ->select('feedback')
            ->from('DeskPRO:Feedback', 'feedback')
            ->where('feedback.id IN (:ids)')
            ->setParameter('ids', $this->params['ids']);

        return $qb->getQuery()->getResult();
    }

    /**
     * @param \Application\DeskPRO\Entity\Feedback $feedback
     *
     * @return \Application\DeskPRO\Entity\Feedback
     */
    public function prepareEntity($feedback)
    {
        foreach ($this->params['actions'] as $key => $value) {
            switch ($key) {
                case 'status_category':
                    $feedback->setStatusCategory($this->statusCategory);
                    break;
                case 'category':
                    $feedback->setCategory($this->category);
                    break;
                case 'custom_category':
                    $feedback->addCustomData($this->customCategory);
                    break;
            }
        }
    }
}
