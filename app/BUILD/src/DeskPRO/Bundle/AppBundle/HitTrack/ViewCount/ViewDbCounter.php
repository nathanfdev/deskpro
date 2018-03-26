<?php

namespace DeskPRO\Bundle\AppBundle\HitTrack\ViewCount;

use Application\DeskPRO\NewSettings\SettingsBag;
use Application\DeskPRO\NewSettings\SettingsResolver;
use DeskPRO\Bundle\AppBundle\Entity\HitRecord;
use Doctrine\DBAL\Connection;

class ViewDbCounter implements ViewCounterInterface
{
    const SETTING_ID = 'hitrecord.viewcounts.last_id';

    /**
     * @var Connection
     */
    private $db;

    /**
     * @var SettingsBag
     */
    private $settings;

    /**
     * ViewDbCounter constructor.
     *
     * @param Connection  $db
     * @param SettingsBag $settings
     */
    public function __construct(Connection $db, SettingsResolver $settingsResolver)
    {
        $this->db       = $db;
        $this->settings = $settingsResolver->getGlobalSettings();
    }

    /**
     * @param \DateTime $last_proc_date
     *
     * @return Views
     */
    public function getViews(\DateTime $last_proc_date)
    {
        $last_id = $this->settings->get(self::SETTING_ID, 0);

        $top_id = $this->db->fetchColumn('
            SELECT id
            FROM hit_record
            ORDER BY id DESC
            LIMIT 1
        ');

        $views = new Views();

        if (!$top_id || $top_id <= $last_id) {
            return $views;
        }

        $q = $this->db->executeQuery('
            SELECT page_type, page_id, COUNT(*) AS num, MAX(id) as max_id
            FROM hit_record
            WHERE id BETWEEN ? AND ? AND page_type IN (?)
            GROUP BY page_type, page_id
            LIMIT 2500
        ', [
            $last_id + 1,
            $top_id,
            HitRecord::getContentPageTypes(),
        ], [
            \PDO::PARAM_INT,
            \PDO::PARAM_INT,
            Connection::PARAM_STR_ARRAY,
        ]);

        $max_read_id = $last_id;

        while ($r = $q->fetch(\PDO::FETCH_ASSOC)) {
            if ($max_read_id < $r['max_id']) {
                $max_read_id = $r['max_id'];
            }

            switch ($r['page_type']) {
                case HitRecord::PAGETYPE_NEWS:
                    $views->registerNewsViews($r['page_id'], $r['num']);
                    break;
                case HitRecord::PAGETYPE_ARTICLE:
                    $views->registerArticleViews($r['page_id'], $r['num']);
                    break;
                case HitRecord::PAGETYPE_DOWNLOAD:
                    $views->registerDownloadViews($r['page_id'], $r['num']);
                    break;
                case HitRecord::PAGETYPE_FEEDBACK:
                    $views->registerFeedbackViews($r['page_id'], $r['num']);
                    break;
            }
        }

        // Save the new max ID
        $this->db->delete('settings', ['name' => self::SETTING_ID]);
        $this->db->insert('settings', ['name' => self::SETTING_ID, 'value' => $max_read_id]);

        return $views;
    }
}
