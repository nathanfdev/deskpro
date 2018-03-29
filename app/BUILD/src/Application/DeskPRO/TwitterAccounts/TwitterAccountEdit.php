<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\TwitterAccounts;

use Application\DeskPRO\Entity\TwitterAccount;
use Doctrine\ORM\EntityManager;

class TwitterAccountEdit
{
    /**
     * @var \Application\DeskPRO\Entity\TwitterAccount
     */
    public $twitter_account;

    public function __construct(TwitterAccount $twitter_account)
    {
        $this->twitter_account = $twitter_account;
    }

    /**
     * @param EntityManager $em
     */
    public function save(EntityManager $em)
    {
        $em->persist($this->twitter_account);
        $em->flush();
    }
}
