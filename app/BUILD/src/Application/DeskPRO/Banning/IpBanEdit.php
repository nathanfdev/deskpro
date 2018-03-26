<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Banning;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity\BanIp;
use Doctrine\ORM\EntityManager;

class IpBanEdit
{
    /**
     * @var \Application\DeskPRO\Entity\BanIp
     */
    public $ip_ban;

    /**
     * @var \Application\DeskPRO\DBAL\Connection
     */
    public $db;

    /**
     * @var string
     */
    protected $old_ip;

    public function __construct(BanIp $ip_ban)
    {
        $this->ip_ban = $ip_ban;
        $this->db     = App::getDb();

        $this->old_ip = $this->ip_ban->banned_ip;
    }

    /**
     * @param EntityManager $em
     *
     * @throws \Exception
     */
    public function save(EntityManager $em)
    {
        $this->db->beginTransaction();
        try {
            $em->persist($this->ip_ban);
            $em->flush($this->ip_ban);
            $this->db->commit();
        } catch (\Exception $e) {
            $this->db->rollback();
            throw $e;
        }
    }
}
