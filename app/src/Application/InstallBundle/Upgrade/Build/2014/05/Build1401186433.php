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

namespace Application\InstallBundle\Upgrade\Build;

class Build1401186433 extends AbstractBuild
{
	private $address_map;
	private $email_account_ids;

	public function run()
	{
		$this->out("Correct filters");
		$filters = $this->container->getDb()->fetchAll("
			SELECT id, terms
			FROM ticket_filters
			WHERE sys_name IS NULL
		");

		if ($filters) {
			$this->address_map = $this->_buildAddressMap();
			$this->email_account_ids = $this->_buildAccountIds();

			foreach ($filters as $f) {
				$this->_convertFilter($f);
			}
		}
	}

	private function _buildAddressMap()
	{
		$addresses = $this->getUpgradeData('201404', 'email_gateway_addresses');
		$map = array();
		foreach ($addresses as $a) {
			$map[$a['id']] = $a['email_gateway_id'];
		}

		return $map;
	}

	private function _buildAccountIds()
	{
		$ids = $this->container->getDb()->fetchAllCol("SELECT id FROM email_accounts");
		return array_combine($ids, $ids);
	}

	private function _convertFilter(array $filter)
	{
		$terms = @unserialize($filter['terms']);
		if (!$terms) return;

		$new_terms = array();
		$fail = false;

		foreach ($terms as $t) {
			switch ($t['type']) {
				case 'gateway_address':
					$address_id = @$t['options']['gateway_address'];
					if ($address_id && isset($this->address_map[$address_id])) {
						$new_t = array(
							'type' => 'email_account',
							'op' => $t['op'],
							'options' => array('email_account_ids' => array($this->address_map[$address_id]))
						);
						$new_terms[] = $new_t;
					} else {
						$fail = true;
					}
					break;

				case 'gateway_account':
					$acc_id = @$t['options']['gateway_account'];
					if ($acc_id && isset($this->email_account_ids[$acc_id])) {
						$new_t = array(
							'type' => 'email_account',
							'op' => $t['op'],
							'options' => array('email_account_ids' => array($acc_id))
						);
						$new_terms[] = $new_t;
					} else {
						$fail = true;
					}
					break;

				default:
					$new_terms[] = $t;
					break;
			}
		}

		if (empty($new_terms) || $fail) {
			$this->container->getDb()->delete('ticket_filters', array('id' => $filter['id']));
		} else {
			$this->container->getDb()->update('ticket_filters', array(
					'terms' => serialize($new_terms)
				), array('id' => $filter['id']));
		}
	}
}