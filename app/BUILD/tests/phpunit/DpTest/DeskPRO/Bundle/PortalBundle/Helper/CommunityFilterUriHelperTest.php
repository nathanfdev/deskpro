<?php

/**
 * DeskPRO.
 */

namespace DpTest\DeskPRO\Bundle\PortalBundle\Helper;

use DeskPRO\Bundle\PortalBundle\Helper\CommunityFilterUriHelper;
use DeskPRO\Bundle\PortalBundle\Model\CommunityFilter;
use DpTest\DeskProTestCase;

class CommunityFilterUriHelperTest extends DeskProTestCase
{
    public function testExtractNoPath()
    {
        $helper = new CommunityFilterUriHelper();

        $filter = $helper->extractCommunityFilter('');

        $this->assertEquals(CommunityFilter::getDefaultValues(), $filter->toArray());

        $filter = $helper->extractCommunityFilter('/');

        $this->assertEquals(CommunityFilter::getDefaultValues(), $filter->toArray());
    }

    public function testStatus()
    {
        $helper = new CommunityFilterUriHelper();

        $filter = $helper->extractCommunityFilter('active');

        $this->assertEquals(CommunityFilter::getDefaultValues(), $filter->toArray());
    }

    public function testStatusCategories()
    {
        $helper = new CommunityFilterUriHelper();

        $filter = $helper->extractCommunityFilter('active-2,3');

        $expected = array_merge(
            CommunityFilter::getDefaultValues(),
            [
                'status'            => 'active',
                'status_categories' => [2, 3],
            ]
        );

        $this->assertEquals($expected, $filter->toArray());
    }

    public function testTypes()
    {
        $helper = new CommunityFilterUriHelper();

        $filter = $helper->extractCommunityFilter('type-5,8,10,100');

        $expected = array_merge(
            CommunityFilter::getDefaultValues(),
            [
                'types' => [5, 8, 10, 100],
            ]
        );

        $this->assertEquals($expected, $filter->toArray());
    }

    public function testSort()
    {
        $helper = new CommunityFilterUriHelper();

        $filter = $helper->extractCommunityFilter('most-discussed-asc');

        $expected = array_merge(
            CommunityFilter::getDefaultValues(),
            [
                'sort'           => 'most-discussed',
                'sort_direction' => 'asc',
            ]
        );

        $this->assertEquals($expected, $filter->toArray());
    }

    public function testCouple()
    {
        $helper = new CommunityFilterUriHelper();

        $filter = $helper->extractCommunityFilter('closed-7,8/highest-rating');

        $expected = array_merge(
            CommunityFilter::getDefaultValues(),
            [
                'status'            => 'closed',
                'status_categories' => [7, 8],
                'sort'              => 'highest-rating',
            ]
        );

        $this->assertEquals($expected, $filter->toArray());
    }

    public function testDefaultSort()
    {
        $helper = new CommunityFilterUriHelper();

        $filter = $helper->extractCommunityFilter('/type-8/date');

        $expected = array_merge(
            CommunityFilter::getDefaultValues(),
            [
                'types' => [8],
            ]
        );

        $this->assertEquals($expected, $filter->toArray());
    }

    public function testMultiple()
    {
        $helper = new CommunityFilterUriHelper();

        $filter = $helper->extractCommunityFilter('closed-7,8/type-8/most-popular-asc');

        $expected = array_merge(
            CommunityFilter::getDefaultValues(),
            [
                'types'             => [8],
                'status'            => 'closed',
                'status_categories' => [7, 8],
                'sort'              => 'most-popular',
                'sort_direction'    => 'asc',
            ]
        );

        $this->assertEquals($expected, $filter->toArray());
    }

    public function testMultipleNotInOrder()
    {
        $helper = new CommunityFilterUriHelper();

        $filter = $helper->extractCommunityFilter('/type-8/most-popular-asc/closed-7,8');

        $expected = array_merge(
            CommunityFilter::getDefaultValues(),
            [
                'types'             => [8],
                'status'            => 'closed',
                'status_categories' => [7, 8],
                'sort'              => 'most-popular',
                'sort_direction'    => 'asc',
            ]
        );

        $this->assertEquals($expected, $filter->toArray());
    }

    public function testExceptionOnInvalidSegment()
    {
        $helper = new CommunityFilterUriHelper();

        $this->setExpectedException('\InvalidArgumentException');

        $filter = $helper->extractCommunityFilter('/doesnt-make-sense');
    }

    // generating uri segments

    public function testGenerateUriSegment()
    {
        $filter = new CommunityFilter();
        $helper = new CommunityFilterUriHelper();

        $this->assertEquals('', $helper->generateUriSegment($filter));
    }

    /**
     * @dataProvider generateUriSegmentStatusProvider
     *
     * @param string $status
     * @param string $expected
     */
    public function testGenerateUriSegmentStatus($status, $expected)
    {
        $filter = new CommunityFilter();
        $filter->setStatus($status);

        $helper = new CommunityFilterUriHelper();

        $this->assertEquals($expected, $helper->generateUriSegment($filter));
    }

    public function generateUriSegmentStatusProvider()
    {
        return [
            ['active', ''],
            ['all', 'all/view-list/viewmode-compact'],
            ['closed', 'closed/view-list/viewmode-compact'],
        ];
    }

    /**
     * @dataProvider generateUriSegmentSort
     *
     * @param string $orderBy
     * @param string $orderDir
     * @param string $expected
     */
    public function testGenerateUriSegmentSort($orderBy, $orderDir, $expected)
    {
        $filter = new CommunityFilter();
        $filter->setSort($orderBy);
        $filter->setSortDirection($orderDir);

        $helper = new CommunityFilterUriHelper();

        $this->assertEquals($expected, $helper->generateUriSegment($filter));
    }

    public function generateUriSegmentSort()
    {
        return [
            ['date', 'asc', 'date-asc/view-list/viewmode-compact'],
            ['date', 'desc', ''],
            ['most-popular', 'asc', 'most-popular-asc/view-list/viewmode-compact'],
            ['most-popular', 'desc', 'most-popular/view-list/viewmode-compact'],
            ['most-discussed', 'desc', 'most-discussed/view-list/viewmode-compact'],
            ['highest-rating', 'desc', 'highest-rating/view-list/viewmode-compact'],
            ['most-views', 'desc', 'most-views/view-list/viewmode-compact'],
        ];
    }

    /**
     * @dataProvider generateUriSegmentStatusSort
     *
     * @param string $status
     * @param string $orderBy
     * @param string $orderDir
     * @param string $expected
     */
    public function testGenerateUriSegmentStatusSort($status, $orderBy, $orderDir, $expected)
    {
        $filter = new CommunityFilter();
        $filter->setStatus($status);
        $filter->setSort($orderBy);
        $filter->setSortDirection($orderDir);

        $helper = new CommunityFilterUriHelper();

        $this->assertEquals($expected, $helper->generateUriSegment($filter));
    }

    public function generateUriSegmentStatusSort()
    {
        return [
            ['all', 'date', 'asc', 'all/date-asc/view-list/viewmode-compact'],
            ['active', 'date', 'desc', ''],
            ['active', 'date', 'asc', 'date-asc/view-list/viewmode-compact'],
            ['all', 'most-popular', 'asc', 'all/most-popular-asc/view-list/viewmode-compact'],
            ['closed', 'most-popular', 'desc', 'closed/most-popular/view-list/viewmode-compact'],
            ['all', 'most-discussed', 'desc', 'all/most-discussed/view-list/viewmode-compact'],
            ['active', 'highest-rating', 'asc', 'highest-rating-asc/view-list/viewmode-compact'],
            ['closed', 'most-views', 'desc', 'closed/most-views/view-list/viewmode-compact'],
        ];
    }

    /**
     * @dataProvider generateUriSegmentStatusCategories
     *
     * @param string $status
     * @param array  $categories
     * @param string $expected
     */
    public function testGenerateUriSegmentStatusCategories($status, array $categories, $expected)
    {
        $filter = new CommunityFilter();
        $filter->setStatus($status);
        $filter->setStatusCategories($categories);

        $helper = new CommunityFilterUriHelper();

        $this->assertEquals($expected, $helper->generateUriSegment($filter));
    }

    public function generateUriSegmentStatusCategories()
    {
        return [
            ['active', [5, 6], '5,6/view-list/viewmode-compact'],
            ['all', [6], 'all-6/view-list/viewmode-compact'],
            ['closed', [], 'closed/view-list/viewmode-compact'],
        ];
    }

    public function testGenerateUriSegmentMultiple()
    {
        $filter = new CommunityFilter();
        $filter->setStatus('active');
        $filter->setStatusCategories([5, 6]);
        $filter->setTypes([15]);
        $filter->setSort('most-views');
        $filter->setSortDirection('asc');

        $helper = new CommunityFilterUriHelper();

        $this->assertEquals('5,6/type-15/most-views-asc/view-list/viewmode-compact', $helper->generateUriSegment($filter));
    }

    public function testGenerateUriSegmentFew()
    {
        $filter = new CommunityFilter();
        $filter->setStatus('closed');
        $filter->setStatusCategories([1001]);
        $filter->setSort('date');
        $filter->setSortDirection('asc');

        $helper = new CommunityFilterUriHelper();

        $this->assertEquals('closed-1001/date-asc/view-list/viewmode-compact', $helper->generateUriSegment($filter));
    }
}
