<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\Email\EmailAccount\Repository;

use Application\DeskPRO\Entity\EmailAccount;
use Doctrine\ORM\EntityManager;

class EmailAccountRepository
{
    /**
     * @var EntityManager
     */
    private $em;

    /**
     * @var EmailAccount[]
     */
    private $accounts;

    /**
     * @var EmailAccount[]
     */
    private $enabled_accounts;

    /**
     * @var EmailAccount[]
     */
    private $disabled_accounts;

    /**
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * Loads account info from the db.
     */
    private function preload()
    {
        if ($this->accounts !== null) {
            return;
        }

        $this->enabled_accounts  = [];
        $this->disabled_accounts = [];
        $this->accounts          = [];

        foreach ($this->em->getRepository(EmailAccount::class)->findAll() as $acc) {
            $this->accounts[$acc->id] = $acc;

            if ($acc->is_enabled) {
                $this->enabled_accounts[$acc->id] = $acc;
            } else {
                $this->disabled_accounts[$acc->id] = $acc;
            }
        }
    }

    /**
     * @return EmailAccount[]
     */
    public function getAccounts()
    {
        $this->preload();

        return $this->accounts;
    }

    /**
     * @return EmailAccount[]
     */
    public function getEnabledAccounts()
    {
        $this->preload();

        return $this->enabled_accounts;
    }

    /**
     * @return EmailAccount[]
     */
    public function getDisabledAccounts()
    {
        $this->preload();

        return $this->disabled_accounts;
    }

    /**
     * @param int $id
     *
     * @return EmailAccount|null
     */
    public function getAccount($id)
    {
        $this->preload();

        return isset($this->accounts[$id]) ? $this->accounts[$id] : null;
    }
}
