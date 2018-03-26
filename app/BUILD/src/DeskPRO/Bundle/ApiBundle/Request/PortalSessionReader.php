<?php

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
