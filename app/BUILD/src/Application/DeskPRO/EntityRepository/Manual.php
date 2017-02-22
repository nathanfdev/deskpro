<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2017, DeskPRO Ltd.
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

namespace Application\DeskPRO\EntityRepository;

use Application\DeskPRO\App;
use Orb\Util\Strings;

class Manual extends AbstractEntityRepository
{
    public function getBySlug($slug)
    {
        $id = Strings::extractRegexMatch('#^([0-9]+)#', $slug, 1);
        if (!$id) {
            return;
        }

        return $this->find($id);
    }

    public function getAllCounts()
    {
        $counts = App::getDb()->fetchAllKeyed('
            SELECT m.id as manual_id, COUNT(t.id) as count
            FROM manuals AS m
              LEFT JOIN manual_topics AS t
                ON t.manual_id = m.id
            GROUP BY m.id
            ORDER BY m.id ASC
        ', [], 'manual_id');

        $result = [];
        foreach ($counts as $count) {
            $result[$count['manual_id']] = $count['count'];
        }

        return $result;
    }

    public function getPermissionTableName()
    {
        return 'manual2usergroup';
    }

    public function getCategoryField()
    {
        return 'manual_id';
    }

    /**
     * Not used in manuals be it is called by PublishController.
     */
    public function repair()
    {
    }
}
