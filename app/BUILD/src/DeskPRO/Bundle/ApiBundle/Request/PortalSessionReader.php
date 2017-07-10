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

namespace DeskPRO\Bundle\ApiBundle\Request;

use Doctrine\ORM\EntityManager;
use Orb\Util\Web;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class PortalSessionReader.
 */
class PortalSessionReader
{
    /**
     * @var EntityManager
     */
    private $em;

    /**
     * Constructor.
     *
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * @param string $sessionId
     *
     * @return array|null
     */
    public function getFromSessionId($sessionId)
    {
        $query = $this->em->getConnection()->executeQuery(
            'SELECT person_id, sess_data FROM sess_data WHERE sess_id = :sess_id',
            ['sess_id' => $sessionId],
            ['sess_id' => \PDO::PARAM_STR]
        );

        $row = $query->fetch();
        if (!$row || !isset($row['sess_data'])) {
            return;
        }

        try {
            $data = base64_decode($row['sess_data']);
            $data = Web::unserializeSesisonData($data);
        } catch (\Exception $e) {
            return;
        }

        return isset($data['_sf2_attributes']) ? $data['_sf2_attributes'] : null;
    }

    /**
     * @param Request $request
     *
     * @return array|null
     */
    public function getFromRequest(Request $request)
    {
        $sessionId = $request->cookies->get('dpsid-portal', null);
        if (!$sessionId || !is_scalar($sessionId)) {
            return;
        }

        return $this->getFromSessionId($sessionId);
    }
}
