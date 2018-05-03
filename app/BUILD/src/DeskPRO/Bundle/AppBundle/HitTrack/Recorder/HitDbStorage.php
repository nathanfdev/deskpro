<?php

namespace DeskPRO\Bundle\AppBundle\HitTrack\Recorder;

use DeskPRO\Bundle\AppBundle\Entity\HitRecord;
use Doctrine\DBAL\Connection;

class HitDbStorage implements HitStorageInterface
{
    /**
     * @var Connection
     */
    private $db;

    /**
     * HitDbStorage constructor.
     *
     * @param Connection $db
     */
    public function __construct(Connection $db)
    {
        $this->db = $db;
    }

    /**
     * @param HitRecord $record
     *
     * @return string
     */
    public function record(HitRecord $record)
    {
        $rec = [
            'visitor_id'   => $record->getVisitorId(),
            'ip_address'   => $record->getIpAddress(),
            'page_type'    => $record->getPageType(),
            'page_id'      => $record->getPageId(),
            'url'          => $record->getUrl(),
            'referrer'     => $record->getReferrer(),
            'user_agent'   => $record->getUserAgent(),
            'geo_country'  => $record->getGeoCountry(),
            'meta'         => $record->getMeta() ? json_encode($record->getMeta()) : null,
            'date_created' => $record->getDateCreated()->format('Y-m-d H:i:s'),
        ];

        return $this->db->insert('hit_record', $rec);
    }
}
