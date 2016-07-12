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

namespace Application\ImportBundle\Generator\Exporter\Parser\DeskPRO\Storage;

use Application\DeskPRO\Entity;
use Application\ImportBundle\Reader\DeskPRO\DeskPROReaderInterface;
use Doctrine\Common\Collections\Criteria;

/**
 * Abstract parser people storage.
 * 
 * Class AbstractParserPeopleStorage
 *
 * @property DeskPROReaderInterface $reader
 */
abstract class AbstractParserPeopleStorage extends \Application\ImportBundle\Generator\Exporter\Parser\AbstractParserPeopleStorage
{
    /**
     * {@inheritdoc}
     */
    public function getPersonEmail($id)
    {
        $person = $this->storage->getPerson($id);

        return isset($person['email']) ? $person['email'] : null;
    }

    /**
     * {@inheritdoc}
     */
    protected function loadByIds($ids)
    {
        $request_ids = $this->storage ? $this->storage->getNotContainsIds($ids) : $ids;

        $criteria = new Criteria();
        $criteria->andWhere($criteria->expr()->in('id', $ids));

        /** @var Entity\Person[] $result */
        $result = $this->reader->findUsersByCriteria($criteria);
        $people = [];

        foreach ($result as $person) {
            $people[$person->getId()] = $person;
        }

        $this->storage->addIgnoreIds($request_ids);
        $this->storage->addPeople($people);
    }
}
