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
 * @category TaskQueueJob
 */

namespace Application\DeskPRO\TaskQueueJob;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity\Person;

class CsvImport extends AbstractJob
{
	protected function _getDefaultData()
	{
		return array(
			'filename' => false,
			'field_maps' => false,
			'imported' => 0,
			'lines_done' => 0,
			'fseek' => 0,
		);
	}

	public function run($max_time)
	{
		$filename = $this->_data['filename'];

		$csv_file = dp_get_tmp_dir() . '/' . $filename;

		if (!file_exists($csv_file) || !is_readable($csv_file)) {
			throw new \Exception("CSV file $csv_file does not exist or is not readable");
		}

		$start_time = microtime(true);

		$fp = fopen($csv_file, 'r');
		fseek($fp, $this->_data['fseek']);

		if ($this->_data['fseek'] == 0) {
			// skip the first row, assuming it's labels
			fgetcsv($fp);
		}

		$complete = false;
		$imported = 0;

		while (microtime(true) - $start_time < $max_time) {
			if (feof($fp)) {
				$complete = true;
				break;
			}

			$row = fgetcsv($fp);
			if (!$row) {
				$complete = true;
				break;
			}

			$this->_data['lines_done']++;

			if ($this->_importRow($row)) {
				$this->_data['imported']++;
				$imported++;
			}
		}

		$this->_data['fseek'] = ftell($fp);

		fclose($fp);

		if ($this->getLogger()) {
			$this->getLogger()->logDebug("Imported $imported people");
		}

		$this->getTask()->run_status = "Processed " . $this->_data['lines_done']
			. " entries, imported " . $this->_data['imported'] . " people";

		if ($complete) {
			@unlink($csv_file);
			return self::TASK_COMPLETED;
		} else {
			return self::TASK_CONTINUING;
		}
	}

	protected function _importRow(array $row)
	{
		$field_maps = $this->_data['field_maps'];

		if (isset($row[0]) && $row[0] === null) {
			return false;
		}

		$em = App::getOrm();

		/** @var $person_em \Application\DeskPRO\EntityRepository\Person */
		$person_em = App::getEntityRepository('DeskPRO:Person');

		/** @var $organization_em \Application\DeskPRO\EntityRepository\Organization */
		$organization_em = App::getEntityRepository('DeskPRO:Organization');

		$person = new Person();

		$primary_email = false;
		$password = false;
		$secondary_emails = array();
		$custom_fields = array();
		$addresses = array();

		foreach ($field_maps AS $column_id => $info) {
			if (!$info['map']) {
				continue;
			}

			$column_value = $row[$column_id];
			if ($column_value === '') {
				continue;
			}

			if ($info['map'] == 'primary_email') {

				if (!\Orb\Validator\StringEmail::isValueValid($column_value)) {
					continue;
				}
				if ($person_em->findOneByEmail($column_value)) {
					continue;
				}

				$primary_email = strtolower($column_value);
			} else if ($info['map'] == 'secondary_email') {
				if (!\Orb\Validator\StringEmail::isValueValid($column_value)) {
					break;
				}
				if ($person_em->findOneByEmail($column_value)) {
					break;
				}

				$secondary_emails[] = strtolower($column_value);
			}
		}

		if (!$primary_email) {
			$primary_email = array_shift($secondary_emails);
		}

		if (!$primary_email) {
			return false;
		}

		$person->addEmailAddressString($primary_email);

		array_unique($secondary_emails);
		foreach ($secondary_emails AS $secondary_email) {
			if ($secondary_email == $primary_email) {
				continue;
			}
			$person->addEmailAddressString($secondary_email);
		}

		foreach ($field_maps AS $column_id => $info) {
			if (!$info['map']) {
				continue;
			}

			$column_value = $row[$column_id];
			if ($column_value === '') {
				continue;
			}

			$map_field = $info['map'];
			$label = isset($info['label']) ? $info['label'] : '';

			switch ($map_field) {
				case 'first_name':
				case 'last_name':
				case 'name':
				case 'organization_position':
					$person->$map_field = $column_value;
					break;

				case 'password':
					$password = $column_value;
					break;

				case 'organization':
					$organization = $organization_em->findOneByName($column_value);
					if ($organization) {
						$person->setOrganization($organization);
					} else if (!empty($info['create_auto'])) {
						$organization = new \Application\DeskPRO\Entity\Organization();
						$organization->name = $column_value;

						$em->persist($organization);

						$person->setOrganization($organization);
					}
					break;

				case 'website':
					$this->_addContactData($person, 'website', array('url' => $column_value), $label);
					break;

				case 'twitter':
					$this->_addContactData($person, 'twitter', array('username' => $column_value), $label);
					break;

				case 'linkedin':
					$this->_addContactData($person, 'linkedin', array('profile_url' => $column_value), $label);
					break;

				case 'facebook':
					$this->_addContactData($person, 'facebook', array('profile_url' => $column_value), $label);
					break;

				case 'phone':
					$this->_addContactData($person, 'phone', array('type' => $info['type'], 'number' => $column_value), $label);
					break;

				case 'im':
					$this->_addContactData($person, 'instant_message', array('service' => $info['type'], 'username' => $column_value), $label);
					break;

				case 'address':
				case 'address1':
				case 'address2':
				case 'city':
				case 'state':
				case 'post_code':
				case 'country':
					if (!isset($addresses[$info['label']])) {
						$addresses[$info['label']] = array('label' => $info['label']);
					}
					$addresses[$info['label']][$map_field] = $column_value;

				default:
					if (preg_match('/^custom_(\d+)$/', $map_field, $match)) {
						$custom_fields['field_' . $match[1]] = $column_value;
					}
			}
		}

		if ($password === false) {
			$password = \Orb\Util\Strings::random(10);
		}
		$person->setPassword($password);

		foreach ($addresses AS $address) {
			if (!isset($address['address'])) {
				$address['address'] = trim((isset($address['address1']) ? $address['address1'] : '') . "\n" . (isset($address['address2']) ? $address['address2'] : ''));
			}
			$this->_addContactData($person, 'address', $address, $address['label']);
		}

		$em->persist($person);
		$em->flush();

		if ($custom_fields) {
			$field_manager = App::getContainer()->getSystemService('person_fields_manager');
			$field_manager->saveFormToObject($custom_fields, $person);
		}

		return $person->id;
	}

	protected function _addContactData(Person $person, $type, array $data, $comment = null)
	{
		if ($comment !== null) {
			$data['comment'] = $comment;
		}

		$contact = new \Application\DeskPRO\Entity\PersonContactData();
		$contact->contact_type = $type;
		$contact->applyFormData($data);
		$contact->person = $person;

		App::getOrm()->persist($contact);

		return $contact;
	}
}