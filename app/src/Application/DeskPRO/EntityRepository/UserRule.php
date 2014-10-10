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
 * @category Entities
 */

namespace Application\DeskPRO\EntityRepository;

use Application\DeskPRO\App;

class UserRule extends AbstractEntityRepository
{
	/**
	 * @return UserRule[]
	 */

	public function getAllUserRules()
	{
		return $this->_em->createQuery('
			SELECT u
			FROM DeskPRO:UserRule u
			LEFT JOIN u.add_usergroup ug
		')->execute();
	}

	/**
	 * @return array
	 */

	public function getAllUserRulesAsArray()
	{
		$resultData = array();

		$user_rules = $this->_em->createQuery('
			SELECT u
			FROM DeskPRO:UserRule u
			LEFT JOIN u.add_usergroup ug
		')->execute();

		foreach ($user_rules as $user_rule) {

			$data['id']             = $user_rule->id;
			$data['email_patterns'] = implode(' ', $user_rule->email_patterns);
			$data['run_order']      = $user_rule->run_order;

			$resultData[] = $data;
		}

		return $resultData;
	}

	/**
	 * Find all matching rules on an email address
	 *
	 * @param $email_address
	 * @return array
	 */
	public function getMatching($email_address)
	{
		$all = $this->findAll();

		$matching = array();

		foreach ($all as $p) {
			if ($p->isEmailMatch($email_address)) {
				$matching[] = $p;
			}
		}

		return $matching;
	}
}
