<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/  |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2014, DeskPRO Ltd.             |
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

namespace Application\DeskPRO\FeedbackCategories;

use Application\DeskPRO\Entity\CustomDefFeedback;
use Doctrine\ORM\EntityManager;

class FeedbackCategories
{
    /**
     * @var \Application\DeskPRO\ORM\EntityManager
     */

    protected $em;

    /**
     * @var \Application\DeskPRO\Entity\CustomDefFeedback
     */

    protected $parent_category;

    /**
     * @var \Application\DeskPRO\Entity\CustomDefFeedback[]
     */

    protected $feedback_categories;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
        $this->createInitialFeedbackCategoryIfNotDefined();
    }

    /**
     * Loads data from the database
     */

    private function preload()
    {
        if ($this->feedback_categories !== null) {
            return;
        }

        $this->feedback_categories = $this->em->getRepository('DeskPRO:CustomDefFeedback')->getAllFlatData($this->parent_category);
    }


    /**
     * Resets this repository so the next time data is requested form it, it will
     * be queried again.
     */

    public function reset()
    {
        $this->feedback_categories = null;
    }

    /**
     * @param int $id
     *
     * @return \Application\DeskPRO\Entity\CustomDefFeedback
     */

    public function getById($id)
    {
        return $this->em->getRepository('DeskPRO:CustomDefFeedback')->get($id);
    }

    /**
     * @return \Application\DeskPRO\Entity\CustomDefFeedback[]
     */

    public function getAll()
    {
        $this->preload();

        return $this->feedback_categories;
    }

    /**
     * @return int
     */

    public function count()
    {
        $this->preload();

        return count($this->feedback_categories);
    }

    /**
     * @return \Application\DeskPRO\Entity\CustomDefFeedback
     */

    public function createNew()
    {
        return CustomDefFeedback::createFeedbackCategory();
    }

    /**
     * Attempts to create initial (parent) category for all hierarchy of categories
     */

    protected function createInitialFeedbackCategoryIfNotDefined()
    {
        $this->parent_category = $this->em->getRepository('DeskPRO:CustomDefFeedback')->getCategoryField();

        if (!$this->parent_category) {

            $this->parent_category                = new CustomDefFeedback();
            $this->parent_category->handler_class = 'Application\\DeskPRO\\CustomFields\\Handler\\Choice';
            $this->parent_category->title         = 'Category';
            $this->parent_category->sys_name      = 'cat';
            $this->parent_category->description   = 'Category';

            $this->em->persist($this->parent_category);
            $this->em->flush();
        }
    }

    /**
     * @return CustomDefFeedback
     */

    public function getParentCategory()
    {
        if (!$this->parent_category) {

            $this->createInitialFeedbackCategoryIfNotDefined();
        }

        return $this->parent_category;
    }

    /**
     * @param array $newOrders
     */

    public function updateDisplayOrders($newOrders)
    {
        $x = 10;

        $feedback_categories = $this->em->getRepository('DeskPRO:CustomDefFeedback')->getByIds($newOrders);

        foreach ($newOrders as $id) {

            if (!isset($feedback_categories[$id])) {

                continue;
            }

            $feedback_category                = $feedback_categories[$id];
            $feedback_category->display_order = $x;

            $this->em->persist($feedback_category);

            $x += 10;
        }

        $this->em->flush();
    }
}
