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
	protected $projects = array();

	protected $priorities = array();

	protected $default_project;
	protected $default_priority;
	protected $default_issuetype;
	protected $default_fields_summary = array();
	protected $default_fields_list = array();


	public function setCreateMeta(array $projects = array())
	{
		$this->projects = $projects;
	}

	public function getCreateMeta()
	{
		return $this->projects;
	}

	public function setPriorities(array $priorities = array())
	{
		$this->priorities = $priorities;
	}

	public function getPriorities()
	{
		return $this->priorities;
	}

	/**
	 * todo?
	 * @param $prop
	 * @param $value
	 * @return $this
	 */
	public function setDefault($prop, $value)
	{
		if (0 !== strpos($prop, 'default_')) return $this;
		$this->{$prop} = $value;
	}

	/**
	 * @return array
	 */
	public function toArray()
	{
		$ret = array();

		// todo check fields presence?

		$ret['default_project'] = $this->default_project;
		$ret['default_priority'] = $this->default_priority;
		$ret['default_issuetype'] = $this->default_issuetype;

		$ret['default_fields_summary'] = $this->default_fields_summary;
		$ret['default_fields_list'] = $this->default_fields_list;

		$ret['projects'] = $this->projects;
		$ret['priorities'] = $this->priorities;

		return $ret;
	}

	static public function fromArray(array $data)
	{
		$meta = new self;
		if (isset($data['projects'])) {
			$meta->setCreateMeta($data['projects']);
			unset($data['projects']);
		}

		if (isset($data['priorities'])) {
			$meta->setPriorities($data['priorities']);
			unset($data['priorities']);
		}

		foreach ($data as $k => $v) {
			$meta->setDefault($k, $v);
		}

		return $meta;
	}
} 