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

namespace Application\DeskPRO\ApiKeys;

use Application\DeskPRO\Entity\ApiKey;
use Doctrine\ORM\EntityManager;

class ApiKeys
{
	/**
	 * @var \Application\DeskPRO\ORM\EntityManager
	 */

	protected $em;

    /**
     * @var \Application\DeskPRO\Entity\ApiKey[]
     */

    protected $api_keys;

	public function __construct(EntityManager $em)
	{
		$this->em = $em;
	}

	/**
	 * Loads twitter accounts data from the database
	 */

	private function preload()
	{
		if ($this->api_keys !== null) {

			return;
		}

		$this->api_keys = $this->em->getRepository('DeskPRO:ApiKey')->getAllApiKeys();
	}


	/**
	 * Resets this repository so the next time data is requested form it, it will
	 * be queried again.
	 */

	public function reset()
	{
		$this->api_keys = null;
	}

	/**
	 * @param int $id
	 * @return \Application\DeskPRO\Entity\ApiKey
	 */

	public function getById($id)
	{
		return $this->em->getRepository('DeskPRO:ApiKey')->get($id);
	}

	/**
	 * @param int $id
	 *
	 * @return array
	 */

	public function getWithUserById($id)
	{
		$api_key = $this->em->getRepository('DeskPRO:ApiKey')->get($id);

		$resultData = array();

		if ($api_key) {

			$data['id']                        = $api_key->id;
			$data['note']                      = $api_key->note;
			$data['code']                      = $api_key->code;
			$data['keyString']                 = $api_key->keyString;
			$data['flags']                     = $api_key['flags'];
			$data['logs']                      = array();

			foreach ($api_key->logs->toArray() as $log) {
				$data['logs'][] = $log->toApiData(true, false);
			}

			if ($api_key->person) {
				$data['person'] = $api_key->person->toApiData(false, false);
			}

			$resultData = $data;
		}

		return $resultData;
	}

    /**
     * @return \Application\DeskPRO\Entity\ApiKey[]
     */

    public function getAll()
    {
        $this->preload();

        return $this->api_keys;
    }

	/**
	 * @return array
	 */

	public function getAllWithUserAsArray()
	{
		$this->preload();

		$resultData = array();

		foreach ($this->api_keys as $api_key) {

			$data['id']                        = $api_key->id;
			$data['note']                      = $api_key->note;
			$data['code']                      = $api_key->code;
			$data['keyString']                 = $api_key->keyString;
			$data['flags']                     = $api_key['flags'];

			if ($api_key->person) {
				$data['person'] = $api_key->person->toApiData(false, false);
			}

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

		return count($this->api_keys);
	}

	/**
	 * @return \Application\DeskPRO\Entity\ApiKey
	 */

	public function createNew()
	{
		return ApiKey::createApiKey();
	}
}