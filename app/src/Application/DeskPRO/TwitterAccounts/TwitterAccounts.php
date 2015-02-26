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

namespace Application\DeskPRO\TwitterAccounts;

use Application\DeskPRO\Entity\TwitterAccount;
use Doctrine\ORM\EntityManager;

class TwitterAccounts
{
    /**
     * @var \Application\DeskPRO\ORM\EntityManager
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
     * Loads twitter accounts data from the database
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
     * @param  int                                        $id
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

        $resultData = array();

        if ($twitter_account) {

            $data['id']                        = $twitter_account->id;
            $data['verified']                  = $twitter_account->verifyCredentials();
            $data['user']['profile_image_url'] = $twitter_account->user->profile_image_url;
            $data['user']['name']              = $twitter_account->user->name;
            $data['user']['screen_name']       = $twitter_account->user->screen_name;

            $agentsArray = array();

            foreach ($twitter_account->persons as $agent) {

                $agentsArray[] = array('id' => $agent->id, 'display_name' => $agent->display_name);
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

        $resultData = array();

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

        $resultData = array();

        foreach($agents as $agent) {

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
