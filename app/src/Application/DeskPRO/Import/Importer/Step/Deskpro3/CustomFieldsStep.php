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
 * @subpackage Import
 */

namespace Application\DeskPRO\Import\Importer\Step\Deskpro3;

use Application\DeskPRO\Entity\CustomDefTicket;
use Application\DeskPRO\Entity\CustomDefPerson;
use Application\DeskPRO\Entity\CustomDefOrganization;
use Application\DeskPRO\Entity\CustomDefArticle;

class CustomFieldsStep extends AbstractDeskpro3Step
{
	public static function getTitle()
	{
		return 'Import Custom Fields';
	}

	public function run($page = 1)
	{
		#----------------------------------------
		# Ticket Fields
		#----------------------------------------

		$count = $this->getOldDb()->fetchColumn("SELECT COUNT(*) FROM ticket_def");
		$this->logMessage(sprintf("Importing %d custom ticket fields", $count));

		if ($count) {

			$fields = $this->getOldDb()->fetchAll("SELECT * FROM ticket_def ORDER BY id ASC");

			$start_time = microtime(true);

			$this->getDb()->beginTransaction();

			try {
				foreach ($fields as $f) {
					$this->processTicketField($f);
				}

				$this->getDb()->commit();
			} catch (\Exception $e) {
				$this->getDb()->rollback();
				throw $e;
			}

			$end_time = microtime(true);
			$this->logMessage(sprintf("Done all fields. Took %.3f seconds.", $end_time-$start_time));
		}

		#----------------------------------------
		# People Fields
		#----------------------------------------

		$count = $this->getOldDb()->fetchColumn("SELECT COUNT(*) FROM user_def");
		$this->logMessage(sprintf("Importing %d custom user fields", $count));

		if ($count) {

			$fields = $this->getOldDb()->fetchAll("SELECT * FROM user_def ORDER BY id ASC");

			$start_time = microtime(true);

			$this->getDb()->beginTransaction();

			try {
				foreach ($fields as $f) {
					$this->processUserField($f);
				}

				$this->getDb()->commit();
			} catch (\Exception $e) {
				$this->getDb()->rollback();
				throw $e;
			}

			$end_time = microtime(true);
			$this->logMessage(sprintf("Done all fields. Took %.3f seconds.", $end_time-$start_time));
		}

		#----------------------------------------
		# Company Fields
		#----------------------------------------

		$count = $this->getOldDb()->fetchColumn("SELECT COUNT(*) FROM user_company_def");
		$this->logMessage(sprintf("Importing %d custom company fields", $count));

		if ($count) {

			$fields = $this->getOldDb()->fetchAll("SELECT * FROM user_company_def ORDER BY id ASC");

			$start_time = microtime(true);

			$this->getDb()->beginTransaction();

			try {
				foreach ($fields as $f) {
					$this->processCompanyField($f);
				}

				$this->getDb()->commit();
			} catch (\Exception $e) {
				$this->getDb()->rollback();
				throw $e;
			}

			$end_time = microtime(true);
			$this->logMessage(sprintf("Done all fields. Took %.3f seconds.", $end_time-$start_time));
		}

		#----------------------------------------
		# Article Fields
		#----------------------------------------

		$count = $this->getOldDb()->fetchColumn("SELECT COUNT(*) FROM faq_def");
		$this->logMessage(sprintf("Importing %d custom article fields", $count));

		if ($count) {

			$fields = $this->getOldDb()->fetchAll("SELECT * FROM faq_def ORDER BY id ASC");

			$start_time = microtime(true);

			$this->getDb()->beginTransaction();

			try {
				foreach ($fields as $f) {
					$this->processArticleField($f);
				}

				$this->getDb()->commit();
			} catch (\Exception $e) {
				$this->getDb()->rollback();
				throw $e;
			}

			$end_time = microtime(true);
			$this->logMessage(sprintf("Done all fields. Took %.3f seconds.", $end_time-$start_time));
		}
	}

	protected function processTicketField(array $f)
	{
		#------------------------------
		# Make sure we havent already done them
		#------------------------------

		$check_exist = $this->getMappedNewId('ticket_def', $f['id']);
		if ($check_exist) {
			$this->getLogger()->log("{$f['id']} already mapped, skipping", 'DEBUG');
			return;
		}

		#------------------------------
		# Create it
		#------------------------------

		$new_field = new CustomDefTicket();
		$new_field->display_order = $f['displayorder'];
		$new_field->title = $f['display_name'];
		$new_field->description = $f['description'];

		$has_choices = false;

		if ($f['formtype'] == 'select2') {
			$f = $this->transform2lvToSelect($f);
		}

		switch ($f['formtype']) {
			case 'input':
				$new_field->handler_class = 'Application\\DeskPRO\\CustomFields\\Handler\\Text';
				$new_field->default_value = $f['default_value'];
				break;

			case 'textarea':
				$new_field->handler_class = 'Application\\DeskPRO\\CustomFields\\Handler\\Textarea';
				$new_field->default_value = $f['default_value'];
				break;

			case 'select':
			case 'radio':
			case 'checkbox':
				$new_field->handler_class = 'Application\\DeskPRO\\CustomFields\\Handler\\Choice';
				$has_choices = true;
				break;
		}

		$this->getEm()->persist($new_field);
		$this->getEm()->flush();

		$this->saveMappedId('ticket_def', $f['id'], $new_field->id);
		$this->saveMappedId('ticket_def_name', $f['id'], $f['name']);

		// For choice options, need to insert choices
		if ($has_choices && ($choice_data = @unserialize($f['data']))) {
			foreach ($choice_data as $k => $choice_info) {
				$child = $new_field->createChild();
				$child->title = $choice_info[2];
				$child->display_order = $k;

				$this->getEm()->persist($child);
				$this->getEm()->flush();

				$this->saveMappedId('ticket_def_choice', $f['id'] . '_' . $choice_info[0], $child->id);
			}
		}
	}

	protected function processUserField(array $f)
	{
		#------------------------------
		# Make sure we havent already done them
		#------------------------------

		$check_exist = $this->getMappedNewId('people_def', $f['id']);
		if ($check_exist) {
			$this->getLogger()->log("{$f['id']} already mapped, skipping", 'DEBUG');
			return;
		}

		#------------------------------
		# Create it
		#------------------------------

		$new_field = new CustomDefPerson();
		$new_field->display_order = $f['displayorder'];
		$new_field->title = $f['display_name'];
		$new_field->description = $f['description'];

		$has_choices = false;

		if ($f['formtype'] == 'select2') {
			$f = $this->transform2lvToSelect($f);
		}

		switch ($f['formtype']) {
			case 'input':
				$new_field->handler_class = 'Application\\DeskPRO\\CustomFields\\Handler\\Text';
				$new_field->default_value = $f['default_value'];
				break;

			case 'textarea':
				$new_field->handler_class = 'Application\\DeskPRO\\CustomFields\\Handler\\Textarea';
				$new_field->default_value = $f['default_value'];
				break;

			case 'select':
			case 'radio':
			case 'checkbox':
				$new_field->handler_class = 'Application\\DeskPRO\\CustomFields\\Handler\\Choice';
				$has_choices = true;
				break;
		}

		$this->getEm()->persist($new_field);
		$this->getEm()->flush();

		$this->saveMappedId('people_def', $f['id'], $new_field->id);

		// For choice options, need to insert choices
		if ($has_choices && ($choice_data = @unserialize($f['data']))) {
			foreach ($choice_data as $k => $choice_info) {
				$child = $new_field->createChild();
				$child->title = $choice_info[2];
				$child->display_order = $k;

				$this->getEm()->persist($child);
				$this->getEm()->flush();

				$this->saveMappedId('people_def_choice', $f['id'] . '_' . $choice_info[0], $child->id);
			}
		}
	}

	protected function processCompanyField(array $f)
	{
		#------------------------------
		# Make sure we havent already done them
		#------------------------------

		$check_exist = $this->getMappedNewId('org_def', $f['id']);
		if ($check_exist) {
			$this->getLogger()->log("{$f['id']} already mapped, skipping", 'DEBUG');
			return;
		}

		#------------------------------
		# Create it
		#------------------------------

		$new_field = new CustomDefOrganization();
		$new_field->display_order = $f['displayorder'];
		$new_field->title = $f['display_name'];
		$new_field->description = $f['description'];

		$has_choices = false;

		if ($f['formtype'] == 'select2') {
			$f = $this->transform2lvToSelect($f);
		}

		switch ($f['formtype']) {
			case 'input':
				$new_field->handler_class = 'Application\\DeskPRO\\CustomFields\\Handler\\Text';
				$new_field->default_value = $f['default_value'];
				break;

			case 'textarea':
				$new_field->handler_class = 'Application\\DeskPRO\\CustomFields\\Handler\\Textarea';
				$new_field->default_value = $f['default_value'];
				break;

			case 'select':
			case 'radio':
			case 'checkbox':
				$new_field->handler_class = 'Application\\DeskPRO\\CustomFields\\Handler\\Choice';
				$new_field->default_value = $f['default_value'];
				$has_choices = true;
				break;
		}

		$this->getEm()->persist($new_field);
		$this->getEm()->flush();

		$this->saveMappedId('org_def', $f['id'], $new_field->id);

		// For choice options, need to insert choices
		if ($has_choices && ($choice_data = @unserialize($f['data']))) {
			foreach ($choice_data as $k => $choice_info) {
				$child = $new_field->createChild();
				$child->title = $choice_info[2];
				$child->display_order = $k;

				$this->getEm()->persist($child);
				$this->getEm()->flush();

				$this->saveMappedId('org_def_choice', $f['id'] . '_' . $choice_info[0], $child->id);
			}
		}
	}

	protected function processArticleField(array $f)
	{
		#------------------------------
		# Make sure we havent already done them
		#------------------------------

		$check_exist = $this->getMappedNewId('kb_def', $f['id']);
		if ($check_exist) {
			$this->getLogger()->log("{$f['id']} already mapped, skipping", 'DEBUG');
			return;
		}

		#------------------------------
		# Create it
		#------------------------------

		$new_field = new CustomDefArticle();
		$new_field->display_order = $f['displayorder'];
		$new_field->title = $f['display_name'];
		$new_field->description = $f['description'];

		$has_choices = false;

		if ($f['formtype'] == 'select2') {
			$f = $this->transform2lvToSelect($f);
		}

		switch ($f['formtype']) {
			case 'input':
				$new_field->handler_class = 'Application\\DeskPRO\\CustomFields\\Handler\\Text';
				$new_field->default_value = $f['default_value'];
				break;

			case 'textarea':
				$new_field->handler_class = 'Application\\DeskPRO\\CustomFields\\Handler\\Textarea';
				$new_field->default_value = $f['default_value'];
				break;

			case 'select':
			case 'radio':
			case 'checkbox':
				$new_field->handler_class = 'Application\\DeskPRO\\CustomFields\\Handler\\Choice';
				$new_field->default_value = $f['default_value'];
				$has_choices = true;
				break;
		}

		$this->getEm()->persist($new_field);
		$this->getEm()->flush();

		$this->saveMappedId('kb_def', $f['id'], $new_field->id);

		// For choice options, need to insert choices
		if ($has_choices && ($choice_data = @unserialize($f['data']))) {
			foreach ($choice_data as $k => $choice_info) {
				$child = $new_field->createChild();
				$child->title = $choice_info[2];
				$child->display_order = $k;

				$this->getEm()->persist($child);
				$this->getEm()->flush();

				$this->saveMappedId('kb_def_choice', $f['id'] . '_' . $choice_info[0], $child->id);
			}
		}
	}


	/**
	 * Converts a 2lvl select box into a normal select box
	 *
	 * @param array $f
	 * @return array
	 */
	protected function transform2lvToSelect(array $f)
	{
		$f['formtype'] = 'select';

		$data = unserialize($f['data']);
		$newdata = array();
		foreach ($data[0] as $parent) {
			if (isset($data[$parent['key']])) {
				foreach ($data[$parent['key']] as $child) {
					$newdata[] = array(
						$child['key'],
						'',
						$parent['value'] . ' > ' . $child['value']
					);
				}
			} else {
				$newdata[] = array(
					$parent['key'],
					'',
					$parent['value']
				);
			}
		}

		$f['data'] = serialize($newdata);
		return $f;
	}
}
