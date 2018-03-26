<?php

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
