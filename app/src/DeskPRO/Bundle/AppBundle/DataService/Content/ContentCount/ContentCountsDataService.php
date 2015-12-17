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

namespace DeskPRO\Bundle\AppBundle\DataService\Content\ContentCount;

use Application\DeskPRO\Entity\CategoryAbstract as Category;
use DeskPRO\Bundle\AppBundle\CountBadge\Count;
use DeskPRO\Bundle\AppBundle\DataService\Content\Category\CategoriesDataService;
use Doctrine\ORM\EntityManagerInterface as EntityManager;

/**
 * Class ContentCountsDataService.
 */
class ContentCountsDataService
{
    /**
     * @var EntityManager
     */
    private $em;

    /**
     * @var CategoriesDataService
     */
    private $categories;

    static $datePeriodLabels = [
        'today' => 'Today',
        'yesterday' => 'Yesterday',
        'this_week' => 'This Week',
        'this_month' => 'This Month',
        'last_month' => 'Last Month',
        'this_year' => 'This Year',
        'ever' => 'Ever'
    ];

    /**
     * @param EntityManager $em
     * @param CategoriesDataService $categories
     */
    public function __construct(EntityManager $em, CategoriesDataService $categories)
    {
        $this->em = $em;
        $this->categories = $categories;
    }

    /**
     * @param string $class Concrete content entity class
     * @param BaseContentCountCriteria $criteria
     *
     * @return Count
     */
    public function countContent($class, BaseContentCountCriteria $criteria)
    {
        $qb = $this->em->createQueryBuilder();

        $qb->select('count(c) as value')
            ->from($class, 'c');

        $criteria->applyFilters($qb);
        if ($criteria->hasGroupBy()) {
            $criteria->applyGroupBy($qb);
        }

        $result = $qb->getQuery()->getArrayResult();

        // if grouped by category, then structure into nested counts reflecting categories tree
        // and perform additional query to select total count
        if ($criteria->isGroupedByCategory()) {
            $count = Count::fromGroupedBy($criteria->getGroupBy());
            $roots = $this->categories->getRoots($class . 'Category');
            $groupToCount = $this->resultToMap($result);
            $count = $this->createNestedRecursively($count, $roots, $groupToCount);
            $count->setCount($this->countDistinct($criteria, $class));
        } // else structure into a Count with a CountsGroup containing all result groups
        elseif ($criteria->hasGroupBy()) {
            $groupedBy = $criteria->getGroupBy();
            $count = Count::fromGroupedBy($groupedBy);
            foreach ($result as $group) {
                $count->add($group['value']);
                if ($groupedBy === 'period_created') {
                    $count->addNested(
                        $group['value'],
                        $group['group_name'],
                        $groupedBy,
                        self::$datePeriodLabels[$group['group_name']]
                    );
                } else {
                    $count->addNested($group['value'], $group['group_name'], $groupedBy);
                }
            }
        } // return a single int result if count isn't grouped
        else {
            $count = Count::fromValue($result[0]['value']);
        }

        return $count;
    }

    /**
     * @param Count $count
     * @param Category[] $childrenCategories
     * @param array $groupToCount
     * @param int $depth
     *
     * @throws \Exception
     *
     * @return Count
     */
    private function createNestedRecursively(Count $count, $childrenCategories, $groupToCount, $depth = 0)
    {
        if ($depth > 10) {
            throw new \Exception('Maximum recursion depth exceeded');
        }

        foreach ($childrenCategories as $category) {
            $countValue = array_key_exists($category->getId(), $groupToCount)
                ? $groupToCount[$category->getId()]
                : 0;
            $count->add($countValue);

            $count->addNestedInstance(
                $this->createNestedRecursively(
                    Count::create($countValue, $category->getId(), 'category', $category->getTitle()),
                    $category->getChildren(),
                    $groupToCount,
                    $depth + 1
                )
            );
        }

        return $count;
    }

    /**
     * @param array $result Array of ['group_name', 'value']
     *
     * @return array Array mapping of 'group_name' to 'value'
     */
    private function resultToMap(array $result)
    {
        $map = [];
        foreach ($result as $count) {
            $map[$count['group_name']] = $count['value'];
        }

        return $map;
    }

    /**
     * @param BaseContentCountCriteria $criteria
     * @param string $class
     *
     * @return int
     */
    private function countDistinct(BaseContentCountCriteria $criteria, $class)
    {
        $totalQb = $this->em->createQueryBuilder();
        $totalQb
            ->select('count(distinct c)')
            ->from($class, 'c');
        $criteria->applyFilters($totalQb);
        $total = $totalQb->getQuery()->getSingleScalarResult();

        return $total;
    }
}
