<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\People;

use Application\DeskPRO\App;

class People
{
    /**
     * Get an array of tickets from the passed IDs.
     *
     * @param array $ids
     *
     * @return array
     */
    public function getNotesForPerson(array $ids)
    {
        return App::getOrm()
            ->getRepository('DeskPRO:Ticket')
            ->getTicketsFromIds($ids);
    }

    public function getPeopleOptions()
    {
        $options = [];

        $options['organizations'] = App::getDataService('Organization')->getOrganizationNames();
        $options['usergroups']    = App::getDataService('Usergroup')->getUsergroupNames();
        $options['languages']     = App::getDataService('Language')->getTitles();

        return $options;
    }
}
