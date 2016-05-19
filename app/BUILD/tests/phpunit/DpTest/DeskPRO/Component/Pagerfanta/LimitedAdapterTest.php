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
namespace DpTest\DeskPRO\Component\Pagerfanta;

use DeskPRO\Component\Pagerfanta\Adapter\LimitedAdapter;
use Pagerfanta\Adapter\AdapterInterface;
use Pagerfanta\Adapter\ArrayAdapter;

class LimitedAdapterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var AdapterInterface
     */
    private $realAdapter;

    /**
     * @var LimitedAdapter
     */
    private $limitedAdapter;

    /**
     * @var LimitedAdapter
     */
    private $unlimitedAdapter;

    /**
     * @var LimitedAdapter
     */
    private $overlimitedAdapter;

    protected function setUp()
    {
        $this->realAdapter        = new ArrayAdapter(range(0, 200)); // 201 items
        $this->limitedAdapter     = new LimitedAdapter($this->realAdapter, 105);
        $this->unlimitedAdapter   = new LimitedAdapter($this->realAdapter, 0);
        $this->overlimitedAdapter = new LimitedAdapter($this->realAdapter, 201);
    }

    /**
     * @test
     */
    public function it_should_return_limit()
    {
        $this->assertEquals(105, $this->limitedAdapter->getLimit());
        $this->assertEquals(0, $this->unlimitedAdapter->getLimit());
        $this->assertEquals(201, $this->overlimitedAdapter->getLimit());
    }

    /**
     * @test
     */
    public function it_should_return_real_adapter()
    {
        $this->assertEquals($this->realAdapter, $this->limitedAdapter->getAdapter());
        $this->assertEquals($this->realAdapter, $this->unlimitedAdapter->getAdapter());
        $this->assertEquals($this->realAdapter, $this->overlimitedAdapter->getAdapter());
    }

    /**
     * @test
     */
    public function it_should_return_nb_results()
    {
        $this->assertEquals(105, $this->limitedAdapter->getNbResults());
        $this->assertEquals(201, $this->unlimitedAdapter->getNbResults());
        $this->assertEquals(201, $this->overlimitedAdapter->getNbResults());
    }

    /**
     * @test
     */
    public function it_should_return_normal_slice()
    {
        $expected = range(50, 59);

        $result = $this->limitedAdapter->getSlice(50, 10);
        $this->assertEquals($expected, $result);
        $this->assertEquals(10, count($result));

        $result = $this->unlimitedAdapter->getSlice(50, 10);
        $this->assertEquals($expected, $result);
        $this->assertEquals(10, count($result));

        $result = $this->overlimitedAdapter->getSlice(50, 10);
        $this->assertEquals($expected, $result);
        $this->assertEquals(10, count($result));
    }

    /**
     * @test
     */
    public function it_should_return_limited_slice()
    {
        $expected = range(100, 104);
        $result   = $this->limitedAdapter->getSlice(100, 10);
        $this->assertEquals(5, count($result));
        $this->assertEquals($expected, $result);

        $expected = range(104, 104);
        $result   = $this->limitedAdapter->getSlice(104, 10);
        $this->assertEquals(1, count($result));
        $this->assertEquals($expected, $result);
    }

    /**
     * @test
     */
    public function it_should_not_return_limited_slice()
    {
        $expected = range(100, 109);

        $result = $this->unlimitedAdapter->getSlice(100, 10);
        $this->assertEquals(10, count($result));
        $this->assertEquals($expected, $result);

        $result = $this->overlimitedAdapter->getSlice(100, 10);
        $this->assertEquals(10, count($result));
        $this->assertEquals($expected, $result);
    }

    /**
     * @test
     */
    public function it_should_return_empty_slice()
    {
        $expected = [];

        $result = $this->limitedAdapter->getSlice(105, 10);
        $this->assertEquals(0, count($result));
        $this->assertEquals($expected, $result);

        $result = $this->limitedAdapter->getSlice(200, 10);
        $this->assertEquals(0, count($result));
        $this->assertEquals($expected, $result);
    }
}
