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

namespace DpTest\DeskPRO\Component\SassCompiler;

use DeskPRO\Component\Pagerfanta\Adapter\LimitedAdapter;
use DeskPRO\Component\Pagerfanta\LimitedPager;
use DpTest\DeskProTestCase;
use Pagerfanta\Adapter\AdapterInterface;
use Pagerfanta\Adapter\ArrayAdapter;

class LimitedPagerTest extends DeskProTestCase
{
    /**
     * @var AdapterInterface
     */
    private $realAdapter;

    /**
     * @var LimitedPager
     */
    private $limitedPager;

    /**
     * @var LimitedPager
     */
    private $unlimitedPager;

    protected function setUp()
    {
        $this->realAdapter  = new ArrayAdapter(range(0, 200)); // 201 items
        $this->limitedPager = new LimitedPager($this->realAdapter, 105);
        $this->limitedPager->setMaxPerPage(10);

        $this->unlimitedPager = new LimitedPager($this->realAdapter, 0);
        $this->unlimitedPager->setMaxPerPage(10);
    }

    /**
     * @test
     */
    public function it_should_return_limit()
    {
        $this->assertEquals(105, $this->limitedPager->getLimit());
        $this->assertEquals(0, $this->unlimitedPager->getLimit());
    }

    /**
     * @test
     */
    public function it_should_return_real_adapter()
    {
        $this->assertEquals($this->realAdapter, $this->limitedPager->getRealAdapter());
        $this->assertEquals($this->realAdapter, $this->unlimitedPager->getRealAdapter());
    }

    /**
     * @test
     */
    public function it_should_return_limited_adapter()
    {
        $this->assertInstanceOf(LimitedAdapter::class, $this->limitedPager->getAdapter());
        $this->assertInstanceOf(LimitedAdapter::class, $this->unlimitedPager->getAdapter());
    }

    /**
     * @test
     */
    public function it_should_return_limited_nb_results()
    {
        $this->assertEquals(105, $this->limitedPager->getNbResults());
    }

    /**
     * @test
     */
    public function it_should_return_normal_nb_results()
    {
        $this->assertEquals(201, $this->unlimitedPager->getNbResults());
    }

    /**
     * @test
     */
    public function it_should_return_limited_nb_pages()
    {
        $this->assertEquals(11, $this->limitedPager->getNbPages());
    }

    /**
     * @test
     */
    public function it_should_return_normal_nb_pages()
    {
        $this->assertEquals(21, $this->unlimitedPager->getNbPages());
    }
}
