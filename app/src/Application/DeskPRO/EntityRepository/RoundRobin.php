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
 * @category Entities
 */

namespace Application\DeskPRO\EntityRepository;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity\RoundRobinAgent;

class RoundRobin extends AbstractEntityRepository
{
	/**
	 * @param \Application\DeskPRO\Entity\RoundRobin $robin
	 * @param array $agents
	 */
	public function setAgents(\Application\DeskPRO\Entity\RoundRobin $robin, array $agents = array())
	{
		$robin->agents->clear();
		$this->_em->flush();
		$sort = 0;

		$nextIsPresented = false;
		foreach ($agents as $agentData) {
			$agentRef = new RoundRobinAgent();
			$agentRef->robin = $robin;
			$agentRef->agent  = $this->_em->getReference('DeskPRO:Person', $agentData['id']);
			$this->_em->persist($agentRef);
			$agentRef['sort'] = ++$sort;
			$robin->agents->add($agentRef);

			if ((int) $agentData['id'] === $robin->next['id']) {
				$nextIsPresented = true;
			}
		}

		if (!$nextIsPresented) {
			$robin->next = $robin->agents->first()->agent;
		}

		$this->_em->flush();
	}
}
