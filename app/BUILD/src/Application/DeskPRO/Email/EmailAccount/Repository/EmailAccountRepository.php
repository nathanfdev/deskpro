<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\Email\EmailAccount\Repository;

use Doctrine\ORM\EntityManager;

class EmailAccountRepository
{
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    private $em;

    /**
     * @var \Application\DeskPRO\Entity\EmailAccount[]
     */
    private $accounts;

    /**
     * @var \Application\DeskPRO\Entity\EmailAccount[]
     */
    private $enabled_accounts;

    /**
     * @var \Application\DeskPRO\Entity\EmailAccount[]
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

        foreach ($this->em->getRepository('DeskPRO:EmailAccount')->findAll() as $acc) {
            $this->accounts[$acc->id] = $acc;

            if ($acc->is_enabled) {
                $this->enabled_accounts[$acc->id] = $acc;
            } else {
                $this->disabled_accounts[$acc->id] = $acc;
            }
        }
    }

    /**
     * @return \Application\DeskPRO\Entity\EmailAccount[]
     */
    public function getAccounts()
    {
        $this->preload();

        return $this->accounts;
    }

    /**
     * @return \Application\DeskPRO\Entity\EmailAccount[]
     */
    public function getEnabledAccounts()
    {
        $this->preload();

        return $this->enabled_accounts;
    }

    /**
     * @return \Application\DeskPRO\Entity\EmailAccount[]
     */
    public function getDisabledAccounts()
    {
        $this->preload();

        return $this->disabled_accounts;
    }

    /**
     * @param int $id
     *
     * @return \Application\DeskPRO\Entity\EmailAccount|null
     */
    public function getAccount($id)
    {
        $this->preload();

        return isset($this->accounts[$id]) ? $this->accounts[$id] : null;
    }
}
