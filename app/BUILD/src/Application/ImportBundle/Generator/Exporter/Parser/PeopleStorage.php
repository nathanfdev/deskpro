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

namespace Application\ImportBundle\Generator\Exporter\Parser;

/**
 * People reader storage to avoid multiple external api requests.
 *
 * Class PeopleStorage
 */
class PeopleStorage implements PeopleStorageInterface
{
    /**
     * @var array
     */
    private $people = [];

    /**
     * @var array
     */
    private $ignore_ids = [];

    /**
     * {@inheritdoc}
     */
    public function setPeople(array $people)
    {
        $this->people = [];
        $this->addPeople($people);
    }

    /**
     * {@inheritdoc}
     */
    public function addPeople(array $people)
    {
        foreach ($people as $person) {
            $this->people[$person['id']] = $person;
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function addIgnoreIds(array $ignore_ids)
    {
        $this->ignore_ids = array_unique(array_merge($this->ignore_ids, $ignore_ids));

        return $this;
    }
    /**
     * {@inheritdoc}
     */
    public function getPerson($id)
    {
        return isset($this->people[$id]) ? $this->people[$id] : null;
    }

    /**
     * {@inheritdoc}
     */
    public function getPeople()
    {
        return $this->people;
    }

    /**
     * {@inheritdoc}
     */
    public function getPeopleIds()
    {
        return array_keys($this->people);
    }

    /**
     * {@inheritdoc}
     */
    public function getNotContainsIds(array $request_ids)
    {
        $not_exist_ids = [];
        $exist_ids     = $this->getPeopleIds();

        foreach ($request_ids as $id) {
            if (!in_array($id, $exist_ids) && !in_array($id, $this->ignore_ids)) {
                $not_exist_ids[] = $id;
            }
        }

        return $not_exist_ids;
    }
}
