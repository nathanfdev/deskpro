<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Log;

use Application\DeskPRO\DBAL\Connection;
use Application\DeskPRO\Entity\Article;
use Application\DeskPRO\Entity\Download;
use Application\DeskPRO\Entity\Feedback;
use Application\DeskPRO\Entity\News;
use Application\DeskPRO\Entity\PageViewLog;
use Application\DeskPRO\HttpFoundation\Session;

class ViewLog
{
    /**
     * @var \Application\DeskPRO\DBAL\Connection
     */
    protected $db;

    /**
     * @var \Application\DeskPRO\HttpFoundation\Session
     */
    protected $session;

    public function __construct(Connection $db, Session $session = null)
    {
        $this->db      = $db;
        $this->session = $session;
    }

    /**
     * Log a view on an object.
     *
     * @param mixed $object
     *
     * @throws \InvalidArgumentException
     *
     * @return int
     */
    public function view($object, $action = 1)
    {
        $type = null;
        if ($object instanceof Article) {
            $type = PageViewLog::TYPE_ARTICLE;
        } elseif ($object instanceof Download) {
            $type = PageViewLog::TYPE_DOWNLOAD;
        } elseif ($object instanceof News) {
            $type = PageViewLog::TYPE_NEWS;
        } elseif ($object instanceof Feedback) {
            $type = PageViewLog::TYPE_FEEDBACK;
        }

        if (!$type) {
            throw new \InvalidArgumentException('Invalid object type. Got `'.get_class($object).'`');
        }

        $person_id = null;
        if ($this->session && $this->session->getEntity()->person && $this->session->getEntity()->person->getId()) {
            $person_id = $this->session->getEntity()->person->getId();
        }

        $this->db->insert('page_view_log', [
            'object_type'  => $type,
            'object_id'    => $object->getId(),
            'view_action'  => $action,
            'person_id'    => $person_id,
            'date_created' => date('Y-m-d H:i:s'),
        ]);

        return $this->db->lastInsertId();
    }
}
