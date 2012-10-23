<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. http://www.deskpro.com/   |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2012, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at http://www.deskpro.com/license                           |
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
 * @subpackage
 */

namespace Application\DeskPRO\DependencyInjection\SystemServices;

use Doctrine\ORM\EntityManager;
use Application\DeskPRO\DependencyInjection\DeskproContainer;
use Application\DeskPRO\Entity\Person;
use Orb\Util\Arrays;

class AgentDataService
{
	protected $has_init = false;

	/**
	 * @var \Application\DeskPRO\Entity\Person[]
	 */
	public $agents = array();

	/**
	 * @var \Doctrine\ORM\EntityManager
	 */
	protected $em;

	public static function create(DeskproContainer $container, array $options = null)
	{
		$em = $container->getEm();
		$o = new static($em);
		return $o;
	}

	public function __construct(EntityManager $em)
	{
		$this->em = $em;
	}

	protected function preload()
	{
		if ($this->has_init) {
			return;
		}
		$this->has_init = true;

		$this->agents = $this->em->getRepository('DeskPRO:Person')->getAgents();
	}


	/**
	 * @return \Application\DeskPRO\Entity\Person[]
	 */
	public function getAgents()
	{
		$this->preload();
		return $this->agents;
	}


	/**
	 * @param array $for_ids
	 */
	public function getNames(array $for_ids = null)
	{
		$ret = array();

		foreach ($this->getAgents() as $agent) {
			if ($for_ids === null || in_array($agent->getId(), $for_ids)) {
				$ret[$agent->getId()] = $agent->getDisplayName();
			}
		}

		return $ret;
	}


	/**
	 * @return \Application\DeskPRO\Entity\Person
	 */
	public function get($id)
	{
		$this->preload();

		if (isset($this->agents[$id])) {
			return $this->agents[$id];
		}

		return null;
	}
}