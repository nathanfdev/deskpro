<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\TwitterAccounts;

use Application\DeskPRO\Entity\TwitterAccount;
use Doctrine\ORM\EntityManager;

class TwitterAccounts
{
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    protected $em;

    /**
     * @var \Application\DeskPRO\Entity\TwitterAccount[]
     */
    protected $twitter_accounts;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * Loads twitter accounts data from the database.
     */
    private function preload()
    {
        if ($this->twitter_accounts !== null) {
            return;
        }

        $this->twitter_accounts = $this->em->getRepository('DeskPRO:TwitterAccount')->getAll();
    }

    /**
     * Resets this repository so the next time data is requested form it, it will
     * be queried again.
     */
    public function reset()
    {
        $this->twitter_accounts = null;
    }

    /**
     * @param int $id
     *
     * @return \Application\DeskPRO\Entity\TwitterAccount
     */
    public function getById($id)
    {
        return $this->em->getRepository('DeskPRO:TwitterAccount')->get($id);
    }

    /**
     * @param int $id
     *
     * @return array
     */
    public function getWithUserById($id)
    {
        $twitter_account = $this->em->getRepository('DeskPRO:TwitterAccount')->get($id);

        $resultData = [];

        if ($twitter_account) {
            $data['id']                        = $twitter_account->id;
            $data['verified']                  = $twitter_account->verifyCredentials();
            $data['user']['profile_image_url'] = $twitter_account->user->profile_image_url;
            $data['user']['name']              = $twitter_account->user->name;
            $data['user']['screen_name']       = $twitter_account->user->screen_name;

            $agentsArray = [];

            foreach ($twitter_account->persons as $agent) {
                $agentsArray[] = ['id' => $agent->id, 'display_name' => $agent->display_name];
            }

            $data['user']['agents'] = $agentsArray;

            $resultData = $data;
        }

        return $resultData;
    }

    /**
     * @return \Application\DeskPRO\Entity\TwitterAccount[]
     */
    public function getAll()
    {
        $this->preload();

        return $this->twitter_accounts;
    }

    /**
     * @return array
     */
    public function getAllWithUserAsArray()
    {
        $this->preload();

        $resultData = [];

        foreach ($this->twitter_accounts as $twitter_account) {
            $data['id']                        = $twitter_account->id;
            $data['verified']                  = $twitter_account->verifyCredentials();
            $data['user']['profile_image_url'] = $twitter_account->user->profile_image_url;
            $data['user']['name']              = $twitter_account->user->name;
            $data['user']['screen_name']       = $twitter_account->user->screen_name;

            $resultData[] = $data;
        }

        return $resultData;
    }

    /**
     * @return array
     */
    public function getAllAgents()
    {
        $agents = $this->em->getRepository('DeskPRO:Person')->getAgents();

        $resultData = [];

        foreach ($agents as $agent) {
            $data['id']           = $agent->id;
            $data['display_name'] = $agent->display_name;

            $resultData[] = $data;
        }

        return $resultData;
    }

    /**
     * @return int
     */
    public function count()
    {
        $this->preload();

        return count($this->twitter_accounts);
    }

    /**
     * @return \Application\DeskPRO\Entity\TwitterAccount
     */
    public function createNew()
    {
        return TwitterAccount::createTwitterAccount();
    }
}
