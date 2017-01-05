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
