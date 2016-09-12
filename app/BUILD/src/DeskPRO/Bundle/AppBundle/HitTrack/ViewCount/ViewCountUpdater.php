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

namespace DeskPRO\Bundle\AppBundle\HitTrack\ViewCount;

use DeskPRO\Component\Util\MapUtils;
use Doctrine\DBAL\Connection;

class ViewCountUpdater
{
    /**
     * @var Connection
     */
    private $db;

    /**
     * @param Connection $db
     */
    public function __construct(Connection $db)
    {
        $this->db = $db;
    }

    /**
     * @param Views $views
     */
    public function updateViews(Views $views)
    {
        $this->_updateTable('articles',  $views->getArticleViews());
        $this->_updateTable('news',      $views->getNewsViews());
        $this->_updateTable('downloads', $views->getDownloadViews());
        $this->_updateTable('feedback',  $views->getFeedbackViews());
    }

    /**
     * @param string $table
     * @param array  $row_map
     */
    private function _updateTable($table, array $row_map)
    {
        if (!$row_map) {
            return;
        }

        $row_map = MapUtils::filterOutFalsey($row_map);
        if (!$row_map) {
            return;
        }

        $vals = MapUtils::arrayMapFromPairs($this->db->fetchAll("SELECT id, view_count FROM $table"), 'id', 'view_count');

        foreach ($row_map as $id => $num) {
            $cur_num = isset($vals[$id]) ? $vals[$id] : 0;
            $this->db->update($table, ['view_count' => $cur_num + $num], ['id' => $id]);
        }
    }
}
