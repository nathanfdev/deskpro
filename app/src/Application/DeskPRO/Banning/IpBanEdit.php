<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/  |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2014, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at https://www.deskpro.com/eula/                            |
|                                                                          |
| By using this software, you acknowledge having read the license          |
| and agree to be bound thereby.                                           |
|                                                                          |
| Please note that DeskPRO is not free software. We release the full       |
| source code for our software because we trust our users to pay us for    |
| the huge investment in time and energy that has gone into both creating  |
| this software and supporting our customers. By providing the source code |
| we preserve our customers' ability to modify, audit and learn from our   |
| work. We have been developing DeskPRO since 2001, please help us make it |
| another decade.                                                          |
|                                                                          |
| Like the work you see? Think you could make it better? We are always     |
| looking for great developers to join us: http://www.deskpro.com/jobs/    |
|                                                                          |
| ~ Thanks, Everyone at Team DeskPRO                                       |
\**************************************************************************/

/**
 * DeskPRO
 *
 * @package DeskPRO
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
        $new_ip = $this->ip_ban->banned_ip;

        $this->db->beginTransaction();

        try {
            $this->db->executeUpdate(
                "DELETE FROM ban_ips WHERE banned_ip = ?",
                array($this->old_ip)
            );

            $this->db->executeUpdate(
                "DELETE FROM ban_ips WHERE banned_ip = ?",
                array($new_ip)
            );

            $ip_ban            = new BanIp();
            $ip_ban->banned_ip = $new_ip;

            $em->persist($ip_ban);
            $em->flush();

            $this->db->commit();
        } catch (\Exception $e) {
            $this->db->rollback();
            throw $e;
        }
    }
}
