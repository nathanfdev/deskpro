<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage Import
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\DeskPRO\Import\Importer\Step\Deskpro3;

use Application\DeskPRO\Entity\CustomDefTicket;
use Application\DeskPRO\Entity\CustomDefPerson;

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

		$count = $this->getOldDb()->fetchAll("SELECT COUNT(*) FROM ticket_def");
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

		$count = $this->getOldDb()->fetchAll("SELECT COUNT(*) FROM user_def");
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

		$has_choices = false;

		switch ($f['formtype']) {
			case 'input':
				$new_field->handler_class = 'Application\\DeskPRO\\CustomFields\\Handler\\Text';
				break;

			case 'textarea':
				$new_field->handler_class = 'Application\\DeskPRO\\CustomFields\\Handler\\Textarea';
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

		$has_choices = false;

		switch ($f['formtype']) {
			case 'input':
				$new_field->handler_class = 'Application\\DeskPRO\\CustomFields\\Handler\\Text';
				break;

			case 'textarea':
				$new_field->handler_class = 'Application\\DeskPRO\\CustomFields\\Handler\\Textarea';
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
}
