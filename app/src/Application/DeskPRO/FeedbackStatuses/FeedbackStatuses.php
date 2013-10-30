<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. http://www.deskpro.com/   |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2012, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at http://www.deskpro.com/license                           |
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

namespace Application\DeskPRO\FeedbackStatuses;

use Application\DeskPRO\Entity\FeedbackStatusCategory;
use Doctrine\ORM\EntityManager;

class FeedbackStatuses
{
	/**
	 * @var \Application\DeskPRO\ORM\EntityManager
	 */
	protected $em;

    /**
     * @var \Application\DeskPRO\Entity\FeedbackStatusCategory[]
     */

    protected $active_statuses;

    /**
     * @var \Application\DeskPRO\Entity\FeedbackStatusCategory[]
     */

    protected $closed_statuses;

	public function __construct(EntityManager $em)
	{
		$this->em = $em;
	}

	/**
	 * Loads feedback statuses data from the database
	 */

	private function preload()
	{
		if ($this->active_statuses !== null && $this->closed_statuses !== null) {
			return;
		}

		$this->active_statuses = $this->em->getRepository('DeskPRO:FeedbackStatusCategory')->getActiveCategories();
        $this->closed_statuses = $this->em->getRepository('DeskPRO:FeedbackStatusCategory')->getClosedCategories();
	}


	/**
	 * Resets this repository so the next time data is requested form it, it will
	 * be queried again.
	 */

	public function reset()
	{
		$this->active_statuses = null;
        $this->closed_statuses = null;
	}

	/**
	 * @param int $id
	 * @return \Application\DeskPRO\Entity\FeedbackStatusCategory
	 */

	public function getById($id)
	{
		return $this->em->getRepository('DeskPRO:FeedbackStatusCategory')->get($id);
	}

	/**
	 * @return \Application\DeskPRO\Entity\FeedbackStatusCategory[]
	 */

	public function getAll()
	{
		$this->preload();

		return $this->active_statuses + $this->closed_statuses;
	}

    /**
     * @return \Application\DeskPRO\Entity\FeedbackStatusCategory[]
     */

    public function getActiveStatuses()
    {
        $this->preload();

        return $this->active_statuses;
    }

    /**
     * @return \Application\DeskPRO\Entity\FeedbackStatusCategory[]
     */

    public function getClosedStatuses()
    {
        $this->preload();

        return $this->closed_statuses;
    }

	/**
	 * @return int
	 */

	public function count()
	{
		$this->preload();

		return count($this->active_statuses) + count($this->closed_statuses);
	}

	/**
	 * @return \Application\DeskPRO\Entity\FeedbackStatusCategory
	 */

	public function createNew()
	{
		return FeedbackStatusCategory::createFeedbackStatusCategory();
	}

	/**
	 * @param array $newOrders
	 */

	public function updateDisplayOrders($newOrders)
	{
		$x = 10;

		$feedback_statuses = $this->em->getRepository('DeskPRO:FeedbackStatusCategory')->getByIds($newOrders);

		foreach ($newOrders as $id) {

			if (!isset($feedback_statuses[$id])) {

				continue;
			}

			$feedback_status                = $feedback_statuses[$id];
			$feedback_status->display_order = $x;

			$this->em->persist($feedback_status);

			$x += 10;
		}

		$this->em->flush();
	}
}