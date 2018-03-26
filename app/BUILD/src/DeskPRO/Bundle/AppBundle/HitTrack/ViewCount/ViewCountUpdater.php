<?php

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
