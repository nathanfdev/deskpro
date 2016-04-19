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
            array(
                'status'            => 'active',
                'status_categories' => array(2, 3),
            )
        );

        $this->assertEquals($expected, $filter->toArray());
    }

    public function testTypes()
    {
        $helper = new FeedbackFilterUriHelper();

        $filter = $helper->extractFeedbackFilter('type-5,8,10,100');

        $expected = array_merge(
            FeedbackFilter::getDefaultValues(),
            array(
                'types' => array(5, 8, 10, 100),
            )
        );

        $this->assertEquals($expected, $filter->toArray());
    }

    public function testSort()
    {
        $helper = new FeedbackFilterUriHelper();

        $filter = $helper->extractFeedbackFilter('most-discussed-asc');

        $expected = array_merge(
            FeedbackFilter::getDefaultValues(),
            array(
                'sort'           => 'most-discussed',
                'sort_direction' => 'asc',
            )
        );

        $this->assertEquals($expected, $filter->toArray());
    }

    public function testCouple()
    {
        $helper = new FeedbackFilterUriHelper();

        $filter = $helper->extractFeedbackFilter('closed-7,8/highest-rating');

        $expected = array_merge(
            FeedbackFilter::getDefaultValues(),
            array(
                'status'            => 'closed',
                'status_categories' => array(7, 8),
                'sort'              => 'highest-rating',
            )
        );

        $this->assertEquals($expected, $filter->toArray());
    }

    public function testDefaultSort()
    {
        $helper = new FeedbackFilterUriHelper();

        $filter = $helper->extractFeedbackFilter('/type-8/date');

        $expected = array_merge(
            FeedbackFilter::getDefaultValues(),
            array(
                'types' => array(8),
            )
        );

        $this->assertEquals($expected, $filter->toArray());
    }

    public function testMultiple()
    {
        $helper = new FeedbackFilterUriHelper();

        $filter = $helper->extractFeedbackFilter('closed-7,8/type-8/most-popular-asc');

        $expected = array_merge(
            FeedbackFilter::getDefaultValues(),
            array(
                'types'             => array(8),
                'status'            => 'closed',
                'status_categories' => array(7, 8),
                'sort'              => 'most-popular',
                'sort_direction'    => 'asc',
            )
        );

        $this->assertEquals($expected, $filter->toArray());
    }

    public function testMultipleNotInOrder()
    {
        $helper = new FeedbackFilterUriHelper();

        $filter = $helper->extractFeedbackFilter('/type-8/most-popular-asc/closed-7,8');

        $expected = array_merge(
            FeedbackFilter::getDefaultValues(),
            array(
                'types'             => array(8),
                'status'            => 'closed',
                'status_categories' => array(7, 8),
                'sort'              => 'most-popular',
                'sort_direction'    => 'asc',
            )
        );

        $this->assertEquals($expected, $filter->toArray());
    }

    public function testExceptionOnInvalidSegment()
    {
        $helper = new FeedbackFilterUriHelper();

        $this->setExpectedException('\InvalidArgumentException');

        $filter = $helper->extractFeedbackFilter('/doesnt-make-sense');
    }

    //
    // generating uri segments
    //

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
        $filter->setStatusCategories(array(5, 6));

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
        $filter->setStatusCategories(array(5, 6));
        $filter->setTypes(array(15));
        $filter->setSort('most-views');
        $filter->setSortDirection('asc');

        $helper = new FeedbackFilterUriHelper();

        $this->assertEquals('5,6/type-15/most-views-asc', $helper->generateUriSegment($filter));
    }

    public function testGenerateUriSegmentFew()
    {
        $filter = new FeedbackFilter();
        $filter->setStatus('closed');
        $filter->setStatusCategories(array(1001));
        $filter->setSort('date');
        $filter->setSortDirection('asc');

        $helper = new FeedbackFilterUriHelper();

        $this->assertEquals('closed-1001/date-asc', $helper->generateUriSegment($filter));
    }
}
