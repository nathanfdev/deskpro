<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\FeedbackCategories;

use Application\DeskPRO\Entity\Brand;
use Application\DeskPRO\Entity\CustomDefFeedback;
use Doctrine\ORM\EntityManager;

class FeedbackCategories
{
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    protected $em;

    /**
     * @var \Application\DeskPRO\Entity\CustomDefFeedback[]
     */
    protected $parent_category;

    /**
     * @var \Application\DeskPRO\Entity\CustomDefFeedback[]
     */
    protected $feedback_categories;

    /**
     * Constructor.
     *
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * Loads data from the database.
     *
     * @param Brand $brand
     */
    private function preload()
    {
        if ($this->feedback_categories !== null) {
            return;
        }

        $this->feedback_categories = [];

        $brands = $this->em->getRepository(Brand::class)->findAll();
        foreach ($brands as $brand) {
            $this->feedback_categories = array_merge(
                $this->feedback_categories,
                $this->em->getRepository('DeskPRO:CustomDefFeedback')->getAllFlatData($this->getParentCategory($brand))
            );
        }
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
     * Attempts to create initial (parent) category for all hierarchy of categories.
     *
     * @param Brand $brand
     */
    protected function createInitialFeedbackCategoryIfNotDefined(Brand $brand)
    {
        $this->parent_category[$brand->getId()] = $this->em->getRepository('DeskPRO:CustomDefFeedback')->getCategoryField($brand);

        if (!$this->parent_category[$brand->getId()]) {
            $this->parent_category[$brand->getId()]                = new CustomDefFeedback();
            $this->parent_category[$brand->getId()]->handler_class = 'Application\\DeskPRO\\CustomFields\\Handler\\Choice';
            $this->parent_category[$brand->getId()]->setBrand($brand);
            $this->parent_category[$brand->getId()]->title       = 'Category';
            $this->parent_category[$brand->getId()]->sys_name    = 'cat';
            $this->parent_category[$brand->getId()]->description = 'Category';

            $this->em->persist($this->parent_category[$brand->getId()]);
            $this->em->flush();
        }
    }

    /**
     * @param Brand $brand
     *
     * @return CustomDefFeedback
     */
    public function getParentCategory(Brand $brand = null)
    {
        if (!$brand) {
            return;
        }
        if (!isset($this->parent_category[$brand->getId()])) {
            $this->createInitialFeedbackCategoryIfNotDefined($brand);
        }

        return $this->parent_category[$brand->getId()];
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
