<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Banning;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity\BanEmail;
use Doctrine\ORM\EntityManager;

class EmailBanEdit
{
    /**
     * @var \Application\DeskPRO\Entity\BanEmail
     */
    public $email_ban;

    /**
     * @var \Application\DeskPRO\DBAL\Connection
     */
    public $db;

    /**
     * @var string
     */
    protected $old_email;

    public function __construct(BanEmail $email_ban)
    {
        $this->email_ban = $email_ban;
        $this->db        = App::getDb();

        $this->old_email = $this->email_ban->banned_email;
    }

    /**
     * @param EntityManager $em
     *
     * @throws \Exception
     */
    public function save(EntityManager $em)
    {
        $new_email = $this->email_ban->banned_email;

        $this->db->beginTransaction();

        try {
            $this->db->executeUpdate(
                'DELETE FROM ban_emails WHERE banned_email = ?',
                [$this->old_email]
            );

            $this->db->executeUpdate(
                'DELETE FROM ban_emails WHERE banned_email = ?',
                [$new_email]
            );

            $email_ban               = new BanEmail();
            $email_ban->banned_email = $new_email;

            $em->persist($email_ban);
            $em->flush();

            $this->db->commit();
        } catch (\Exception $e) {
            $this->db->rollback();
            throw $e;
        }
    }
}
