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
 * @subpackage
 */

namespace DpUnitTests\PortalBundle\Helper;

use Application\PortalBundle\Helper\FeedbackFilterUriHelper;
use Application\PortalBundle\Model\FeedbackFilter;

class FeedbackFilterUriHelperTest extends \PHPUnit_Framework_TestCase
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

        $filter = $helper->extractFeedbackFilter('all');

        $this->assertEquals(FeedbackFilter::getDefaultValues(), $filter->toArray());
    }

    public function testStatusCategories()
    {
        $helper = new FeedbackFilterUriHelper();

        $filter = $helper->extractFeedbackFilter('active-2,3');

        $expected = array_merge(FeedbackFilter::getDefaultValues(), array(
            'status' => 'active',
            'status_categories' => array(2,3)
        ));

        $this->assertEquals($expected, $filter->toArray());
    }

    public function testTypes()
    {
        $helper = new FeedbackFilterUriHelper();

        $filter = $helper->extractFeedbackFilter('type-5,8,10,100');

        $expected = array_merge(FeedbackFilter::getDefaultValues(), array(
            'types' => array(5, 8, 10, 100)
        ));

        $this->assertEquals($expected, $filter->toArray());
    }

    public function testSort()
    {
        $helper = new FeedbackFilterUriHelper();

        $filter = $helper->extractFeedbackFilter('most-discussed-asc');

        $expected = array_merge(FeedbackFilter::getDefaultValues(), array(
            'sort' => 'most-discussed',
            'sort_direction' => 'asc'
        ));

        $this->assertEquals($expected, $filter->toArray());
    }

    public function testCouple()
    {
        $helper = new FeedbackFilterUriHelper();

        $filter = $helper->extractFeedbackFilter('closed-7,8/highest-rating');

        $expected = array_merge(FeedbackFilter::getDefaultValues(), array(
            'status' => 'closed',
            'status_categories' => array(7,8),
            'sort' => 'highest-rating'
        ));

        $this->assertEquals($expected, $filter->toArray());
    }

    public function testDefaultSort()
    {
        $helper = new FeedbackFilterUriHelper();

        $filter = $helper->extractFeedbackFilter('/type-8/date');

        $expected = array_merge(FeedbackFilter::getDefaultValues(), array(
            'types' => array(8)
        ));

        $this->assertEquals($expected, $filter->toArray());
    }

    public function testMultiple()
    {
        $helper = new FeedbackFilterUriHelper();

        $filter = $helper->extractFeedbackFilter('closed-7,8/type-8/most-popular-asc');

        $expected = array_merge(FeedbackFilter::getDefaultValues(), array(
            'types' => array(8),
            'status' => 'closed',
            'status_categories' => array(7,8),
            'sort' => 'most-popular',
            'sort_direction' => 'asc'
        ));

        $this->assertEquals($expected, $filter->toArray());
    }

    public function testMultipleNotInOrder()
    {
        $helper = new FeedbackFilterUriHelper();

        $filter = $helper->extractFeedbackFilter('/type-8/most-popular-asc/closed-7,8');

        $expected = array_merge(FeedbackFilter::getDefaultValues(), array(
            'types' => array(8),
            'status' => 'closed',
            'status_categories' => array(7,8),
            'sort' => 'most-popular',
            'sort_direction' => 'asc'
        ));

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

    public function testGenerateUriSegmentStatus()
    {
        $filter = new FeedbackFilter();
        $filter->setStatus('active');

        $helper = new FeedbackFilterUriHelper();

        $this->assertEquals('active', $helper->generateUriSegment($filter));
    }

    public function testGenerateUriSegmentSort()
    {
        $filter = new FeedbackFilter();
        $filter->setSort('most-popular');

        $helper = new FeedbackFilterUriHelper();

        $this->assertEquals('most-popular', $helper->generateUriSegment($filter));
    }

    public function testGenerateUriSegmentStatusCategories()
    {
        $filter = new FeedbackFilter();
        $filter->setStatus('active');
        $filter->setStatusCategories(array(5,6));

        $helper = new FeedbackFilterUriHelper();

        $this->assertEquals('active-5,6', $helper->generateUriSegment($filter));
    }

    public function testGenerateUriSegmentMultiple()
    {
        $filter = new FeedbackFilter();
        $filter->setStatus('active');
        $filter->setStatusCategories(array(5,6));
        $filter->setTypes(array(15));
        $filter->setSort('most-views');
        $filter->setSortDirection('asc');

        $helper = new FeedbackFilterUriHelper();

        $this->assertEquals('active-5,6/type-15/most-views-asc', $helper->generateUriSegment($filter));
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
