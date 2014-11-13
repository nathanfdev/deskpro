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

namespace Application\DeskPRO\JIRA;



class Meta
{
	/**
	 * create meta-data
	 * @var array
	 */
	protected $projects = array();

	/**
	 * schema for fields
	 * @var array
	 */
	protected $fields = array();

	protected $default_project;
	protected $default_issuetype;
	protected $default_fields_summary = array();
	protected $default_fields_list = array();

	/**
	 * @return array
	 */
	public function toArray()
	{
		$ret = array();

		$ref = new \ReflectionObject($this);
		foreach ($ref->getProperties() as $prop) {
			$name = $prop->getName();
			$ret[$name] = $this->{$name};
		}

		return $ret;
	}

	/**
	 * @param array $data
	 * @return Meta
	 */
	static public function fromArray(array $data)
	{
		$meta = new self;
		unset($data['system_fields']);
		foreach ($data as $k => $v) {
			if (property_exists($meta, $k)) {
				$meta->{$k} = $v;
			}
		}
		return $meta;
	}

	/**
	 * @return array
	 */
	public function getAllFields()
	{
		return array_values(array_unique(array_merge($this->default_fields_summary, $this->default_fields_list)));
	}
} 