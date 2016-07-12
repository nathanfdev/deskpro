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

namespace Application\ImportBundle\Reader\ZenDesk\Fixtures\HelpCenter;

use Application\ImportBundle\Reader\ZenDesk\Fixtures\AbstractFixtureLoader;
use Application\ImportBundle\Reader\ZenDesk\Request\Request;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Criteria;

/**
 * Class CategoryLoader.
 */
class CategoryLoader extends AbstractFixtureLoader
{
    const FAKE_PREFIX = 'Fake Category';

    /**
     * @var ArrayCollection
     */
    private $categories;

    /**
     * {@inheritdoc}
     */
    public function load()
    {
        $response         = $this->request_adapter->doRequest(Request::createHelpCenter('Category', 'findAll'));
        $this->categories = new ArrayCollection($this->toArray($response->categories));
    }

    /**
     * ZD account should have at least one category.
     *
     * @return bool
     */
    public function hasPrimaryCategory()
    {
        if (null === $this->categories) {
            $this->load();
        }

        return $this->categories->count() > $this->getFakeCategories()->count();
    }

    /**
     * Returns a random fake category.
     *
     * @return \stdClass
     */
    public function getRandomCategory()
    {
        $categories = $this->getFakeCategories()->getValues();
        if (empty($categories)) {
            throw new \RuntimeException('No fake categories loaded');
        }

        return $categories[rand(0, count($categories) - 1)];
    }

    /**
     * Returns a collection of fake categories
     * We can easily create or delete them.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getFakeCategories()
    {
        if (null === $this->categories) {
            $this->load();
        }

        $criteria = new Criteria(Criteria::expr()->contains('name', self::FAKE_PREFIX));
        $matching = $this->categories->matching($criteria);

        return $matching;
    }
}
