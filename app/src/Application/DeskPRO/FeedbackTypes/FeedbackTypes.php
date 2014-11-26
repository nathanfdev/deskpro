<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/  |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2014, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at https://www.deskpro.com/eula/                            |
|                                                                          |
| By using this software, you acknowledge having read the license          |
| and agree to be bound thereby.                                           |
|                                                                          |
| Please note that DeskPRO is not free software. We release the full       |
| source code for our software because we trust our users to pay us for    |
| the huge investment in time and energy that has gone into both creating  |
| this software and supporting our customers. By providing the source code |
| we preserve our customers' ability to modify, audit and learn from our   |
| work. We have been developing DeskPRO since 2001, please help us make it |
| another decade.                                                          |
|                                                                          |
| Like the work you see? Think you could make it better? We are always     |
| looking for great developers to join us: http://www.deskpro.com/jobs/    |
|                                                                          |
| ~ Thanks, Everyone at Team DeskPRO                                       |
\**************************************************************************/

/**
 * DeskPRO
 *
 * @package DeskPRO
 */

namespace Application\DeskPRO\FeedbackTypes;

use Application\DeskPRO\Entity\FeedbackCategory;
use Doctrine\ORM\EntityManager;

class FeedbackTypes
{
    /**
     * @var \Application\DeskPRO\ORM\EntityManager
     */
    protected $em;

    /**
     * @var \Application\DeskPRO\Entity\FeedbackCategory[]
     */

    protected $feedback_types;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * Loads feedback statuses data from the database
     */

    private function preload()
    {
        if ($this->feedback_types !== null) {
            return;
        }

        $this->feedback_types = $this->em->getRepository('DeskPRO:FeedbackCategory')->getAll();
    }


    /**
     * Resets this repository so the next time data is requested form it, it will
     * be queried again.
     */

    public function reset()
    {
        $this->feedback_types = null;
    }

    /**
     * @param  int                                          $id
     * @return \Application\DeskPRO\Entity\FeedbackCategory
     */

    public function getById($id)
    {
        return $this->em->getRepository('DeskPRO:FeedbackCategory')->get($id);
    }

    /**
     * @param \Application\DeskPRO\Entity\FeedbackCategory|int $feedback_type
     *
     * @return array
     */

    public function getNonAgentUserGroups($feedback_type)
    {
        if (is_int($feedback_type)) {

            $feedback_type = $this->getById($feedback_type);
        }

        return $this->em->getRepository('DeskPRO:FeedbackCategory')->getUserGroups($feedback_type->getId(), false);
    }

    /**
     * @param \Application\DeskPRO\Entity\FeedbackCategory|int $feedback_type
     *
     * @return array
     */

    public function getAgentUserGroups($feedback_type)
    {
        if (is_int($feedback_type)) {

            $feedback_type = $this->getById($feedback_type);
        }

        return $this->em->getRepository('DeskPRO:FeedbackCategory')->getUserGroups($feedback_type->getId(), true);
    }

    /**
     * @return \Application\DeskPRO\Entity\FeedbackCategory[]
     */

    public function getAll()
    {
        $this->preload();

        return $this->feedback_types;
    }

    /**
     * @return int
     */

    public function count()
    {
        $this->preload();

        return count($this->feedback_types);
    }

    /**
     * @return \Application\DeskPRO\Entity\FeedbackCategory
     */

    public function createNew()
    {
        return FeedbackCategory::createFeedbackCategory();
    }

    /**
     * @param array $newOrders
     */

    public function updateDisplayOrders($newOrders)
    {
        $x = 10;

        $feedback_types = $this->em->getRepository('DeskPRO:FeedbackCategory')->getByIds($newOrders);

        foreach ($newOrders as $id) {

            if (!isset($feedback_types[$id])) {

                continue;
            }

            $feedback_type                = $feedback_types[$id];
            $feedback_type->display_order = $x;

            $this->em->persist($feedback_type);

            $x += 10;
        }

        $this->em->flush();
    }
}
