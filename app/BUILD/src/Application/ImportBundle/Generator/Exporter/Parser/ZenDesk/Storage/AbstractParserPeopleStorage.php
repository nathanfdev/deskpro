<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2015, DeskPRO Ltd.
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

namespace Application\ImportBundle\Generator\Exporter\Parser\ZenDesk\Storage;

use Application\ImportBundle\Generator\Exporter\Parser\ZenDesk\People;
use Application\ImportBundle\Reader\ZenDesk\ZenDeskReaderInterface;

/**
 * Abstract ZenDesk parser people storage.
 * 
 * Class AbstractParserPeopleStorage
 *
 * @property ZenDeskReaderInterface $reader
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
        $request_ids = $this->storage->getNotContainsIds($ids);
        $result      = $this->reader->getPeopleByIds($request_ids);

        $people = array();
        foreach ($result as $person) {
            if (!isset($person['email']) || !$person['email']) {
                $person = array_merge($person, array(
                    'email'      => sprintf('imported.user.%s@example.com', $person['id']),
                    'is_deleted' => true,
                ));
            }

            $people[$person['id']] = $person;
        }

        $this->storage->addPeople($people);

        // ZD does not keep foreign integrity so create fake profiles for deleted users
        $deleted_ids = $this->storage->getNotContainsIds($ids);
        $created_at  = new \DateTime();
        $created_at  = $created_at->format('c');

        $people = array();
        foreach ($deleted_ids as $id) {
            $person = $this->reader->getPersonById($id);
            if (!$person) {
                $person = array(
                    'id'         => $id,
                    'name'       => 'User '.$id,
                    'created_at' => $created_at,
                    'updated_at' => $created_at,
                    'locale'     => 'en-US',
                    'time_zone'  => 'UTC',
                    'role'       => People::ROLE_END_USER,
                );
            }

            $people[$id] = array_merge($person, array(
                'email'      => sprintf('imported.user.%s@example.com', $id),
                'is_deleted' => true,
            ));
        }

        $this->storage->addPeople($people);
    }
}
