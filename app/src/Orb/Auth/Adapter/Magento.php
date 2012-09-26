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

namespace Orb\Auth\Adapter;

class Magento extends DbTable
{
	const OPT_TABLE_PREFIX = 'table_prefix';

	protected $_website_id = 1;

	protected function initOptions()
	{
		parent::initOptions();

		$this->options[self::OPT_TABLE]           = $this->options->get(self::OPT_TABLE_PREFIX, '') . 'customer_entity';
		$this->options[self::OPT_FIELD_ID]        = 'entity_id';
		$this->options[self::OPT_FIELD_USERNAME]  = null;
		$this->options[self::OPT_FIELD_PASSWORD]  = 'password';
		$this->options[self::OPT_FIELD_EMAIL]     = 'email';
		$this->options[self::OPT_FIELD_FIRST_NAME] = 'first_name';
		$this->options[self::OPT_FIELD_LAST_NAME] = 'last_name';
	}

	protected function _getBaseQuery($where)
	{
		$table = $this->options[self::OPT_TABLE];
		$customer_eav_table = $this->options->get(self::OPT_TABLE_PREFIX, '') . 'customer_entity';
		$eav_attribute_table = $this->options->get(self::OPT_TABLE_PREFIX, '') . 'eav_attribute';

		return "
			SELECT customer_entity.entity_id, customer_entity.email, customer_eav_password.value AS password,
				customer_eav_first_name.value AS first_name,
				customer_eav_last_name.value AS last_name
			FROM $table AS customer_entity
			INNER JOIN $eav_attribute_table AS eav_password ON
				(customer_entity.entity_type_id = eav_password.entity_type_id AND eav_password.attribute_code = 'password_hash')
			INNER JOIN {$customer_eav_table}_varchar AS customer_eav_password ON
				(eav_password.attribute_id = customer_eav_password.attribute_id
				AND customer_entity.entity_type_id = customer_eav_password.entity_type_id
				AND customer_entity.entity_id = customer_eav_password.entity_id)
			INNER JOIN $eav_attribute_table AS eav_first_name ON
				(customer_entity.entity_type_id = eav_first_name.entity_type_id AND eav_first_name.attribute_code = 'firstname')
			INNER JOIN {$customer_eav_table}_varchar AS customer_eav_first_name ON
				(eav_first_name.attribute_id = customer_eav_first_name.attribute_id
				AND customer_entity.entity_type_id = customer_eav_first_name.entity_type_id
				AND customer_entity.entity_id = customer_eav_first_name.entity_id)
			INNER JOIN $eav_attribute_table AS eav_last_name ON
				(customer_entity.entity_type_id = eav_last_name.entity_type_id AND eav_last_name.attribute_code = 'lastname')
			INNER JOIN {$customer_eav_table}_varchar AS customer_eav_last_name ON
				(eav_last_name.attribute_id = customer_eav_last_name.attribute_id
				AND customer_entity.entity_type_id = customer_eav_last_name.entity_type_id
				AND customer_entity.entity_id = customer_eav_last_name.entity_id)
			WHERE $where
			LIMIT 1
		";
	}

	public function getUserInfoForEmail($email)
	{
		$sql = $this->_getBaseQuery('customer_entity.email = ? AND customer_entity.website_id = ?');

		$result = $this->db->fetchAssoc($sql, array($email, $this->_website_id));
		if (!$result) {
			return null;
		}

		return $result;
	}

	public function getUserInfoForId($id)
	{
		$sql = $this->_getBaseQuery('customer_entity.entity_id = ? AND customer_entity.website_id = ?');

		$result = $this->db->fetchAssoc($sql, array($id, $this->_website_id));
		if (!$result) {
			return null;
		}

		return $result;
	}

	protected function isValidPassword(array $userinfo, $password_input)
	{
		$parts = explode(':', $userinfo['password'], 2);
		if (count($parts) != 2) {
			return false;
		}

		return (md5($parts[1] . $password_input) === $parts[0]);
	}
}