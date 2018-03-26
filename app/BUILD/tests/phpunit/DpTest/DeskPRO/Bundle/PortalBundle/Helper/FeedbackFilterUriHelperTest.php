<?php

/**
 * DeskPRO.
 */

namespace DpTest\DeskPRO\Bundle\PortalBundle\Helper;

use DeskPRO\Bundle\PortalBundle\Helper\FeedbackFilterUriHelper;
use DeskPRO\Bundle\PortalBundle\Model\FeedbackFilter;
use DpTest\DeskProTestCase;

class FeedbackFilterUriHelperTest extends DeskProTestCase
{
    public function testExtractNoPath()
    {
        $helper = new FeedbackFilterUriHelper();

        $filter = $helper->extractFeedbackFilter('');

        $this->assertEquals(FeedbackFilter::getDefaultValues(), $filter->toArray());

        $filter = $helper->extractFeedbackFilter('/');

        $this->assertEquals(FeedbackFilter::getDefaultValues(), $filter->toArray());
    }

    public function testStatus()
    {
        $helper = new FeedbackFilterUriHelper();

        $filter = $helper->extractFeedbackFilter('active');

        $this->assertEquals(FeedbackFilter::getDefaultValues(), $filter->toArray());
    }

    public function testStatusCategories()
    {
        $helper = new FeedbackFilterUriHelper();

        $filter = $helper->extractFeedbackFilter('active-2,3');

        $expected = array_merge(
            FeedbackFilter::getDefaultValues(),
            [
                'status'            => 'active',
                'status_categories' => [2, 3],
            ]
        );

        $this->assertEquals($expected, $filter->toArray());
    }

    public function testTypes()
    {
        $helper = new FeedbackFilterUriHelper();

        $filter = $helper->extractFeedbackFilter('type-5,8,10,100');

        $expected = array_merge(
            FeedbackFilter::getDefaultValues(),
            [
                'types' => [5, 8, 10, 100],
            ]
        );

        $this->assertEquals($expected, $filter->toArray());
    }

    public function testSort()
    {
        $helper = new FeedbackFilterUriHelper();

        $filter = $helper->extractFeedbackFilter('most-discussed-asc');

        $expected = array_merge(
            FeedbackFilter::getDefaultValues(),
            [
                'sort'           => 'most-discussed',
                'sort_direction' => 'asc',
            ]
        );

        $this->assertEquals($expected, $filter->toArray());
    }

    public function testCouple()
    {
        $helper = new FeedbackFilterUriHelper();

        $filter = $helper->extractFeedbackFilter('closed-7,8/highest-rating');

        $expected = array_merge(
            FeedbackFilter::getDefaultValues(),
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
        $helper = new FeedbackFilterUriHelper();

        $filter = $helper->extractFeedbackFilter('/type-8/date');

        $expected = array_merge(
            FeedbackFilter::getDefaultValues(),
            [
                'types' => [8],
            ]
        );

        $this->assertEquals($expected, $filter->toArray());
    }

    public function testMultiple()
    {
        $helper = new FeedbackFilterUriHelper();

        $filter = $helper->extractFeedbackFilter('closed-7,8/type-8/most-popular-asc');

        $expected = array_merge(
            FeedbackFilter::getDefaultValues(),
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
        $helper = new FeedbackFilterUriHelper();

        $filter = $helper->extractFeedbackFilter('/type-8/most-popular-asc/closed-7,8');

        $expected = array_merge(
            FeedbackFilter::getDefaultValues(),
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
        $helper = new FeedbackFilterUriHelper();

        $this->setExpectedException('\InvalidArgumentException');

        $filter = $helper->extractFeedbackFilter('/doesnt-make-sense');
    }

    // generating uri segments

    public function testGenerateUriSegment()
    {
        $filter = new FeedbackFilter();
        $helper = new FeedbackFilterUriHelper();

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
        $filter = new FeedbackFilter();
        $filter->setStatus($status);

        $helper = new FeedbackFilterUriHelper();

        $this->assertEquals($expected, $helper->generateUriSegment($filter));
    }

    public function generateUriSegmentStatusProvider()
    {
        return [
            ['active', ''],
            ['all', 'all'],
            ['closed', 'closed'],
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
        $filter = new FeedbackFilter();
        $filter->setSort($orderBy);
        $filter->setSortDirection($orderDir);

        $helper = new FeedbackFilterUriHelper();

        $this->assertEquals($expected, $helper->generateUriSegment($filter));
    }

    public function generateUriSegmentSort()
    {
        return [
            ['date', 'asc', 'date-asc'],
            ['date', 'desc', ''],
            ['most-popular', 'asc', 'most-popular-asc'],
            ['most-popular', 'desc', 'most-popular'],
            ['most-discussed', 'desc', 'most-discussed'],
            ['highest-rating', 'desc', 'highest-rating'],
            ['most-views', 'desc', 'most-views'],
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
        $filter = new FeedbackFilter();
        $filter->setStatus($status);
        $filter->setSort($orderBy);
        $filter->setSortDirection($orderDir);

        $helper = new FeedbackFilterUriHelper();

        $this->assertEquals($expected, $helper->generateUriSegment($filter));
    }

    public function generateUriSegmentStatusSort()
    {
        return [
            ['all', 'date', 'asc', 'all/date-asc'],
            ['active', 'date', 'desc', ''],
            ['active', 'date', 'asc', 'date-asc'],
            ['all', 'most-popular', 'asc', 'all/most-popular-asc'],
            ['closed', 'most-popular', 'desc', 'closed/most-popular'],
            ['all', 'most-discussed', 'desc', 'all/most-discussed'],
            ['active', 'highest-rating', 'asc', 'highest-rating-asc'],
            ['closed', 'most-views', 'desc', 'closed/most-views'],
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
        $filter = new FeedbackFilter();
        $filter->setStatus('active');
        $filter->setStatusCategories([5, 6]);

        $helper = new FeedbackFilterUriHelper();

        $this->assertEquals('5,6', $helper->generateUriSegment($filter));
    }

    public function generateUriSegmentStatusCategories()
    {
        return [
            ['active', [5, 6], '5,6'],
            ['all', [6], 'all-6'],
            ['closed', [], 'closed'],
        ];
    }

    public function testGenerateUriSegmentMultiple()
    {
        $filter = new FeedbackFilter();
        $filter->setStatus('active');
        $filter->setStatusCategories([5, 6]);
        $filter->setTypes([15]);
        $filter->setSort('most-views');
        $filter->setSortDirection('asc');

        $helper = new FeedbackFilterUriHelper();

        $this->assertEquals('5,6/type-15/most-views-asc', $helper->generateUriSegment($filter));
    }

    public function testGenerateUriSegmentFew()
    {
        $filter = new FeedbackFilter();
        $filter->setStatus('closed');
        $filter->setStatusCategories([1001]);
        $filter->setSort('date');
        $filter->setSortDirection('asc');

        $helper = new FeedbackFilterUriHelper();

        $this->assertEquals('closed-1001/date-asc', $helper->generateUriSegment($filter));
    }
}
