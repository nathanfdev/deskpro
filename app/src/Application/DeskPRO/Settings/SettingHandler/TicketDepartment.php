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
 */

namespace Application\DeskPRO\Settings\SettingHandler;

use Application\DeskPRO\DBAL\Connection;
use Application\DeskPRO\Settings\Settings as SettingsHandler;

class TicketDepartment
{
	/**
	 * @var \Application\DeskPRO\Settings\Settings
	 */
	protected $settings;

	/**
	 * @var \Application\DeskPRO\DBAL\Connection
	 */
	protected $db;

	public function __construct(SettingsHandler $settings, Connection $db)
	{
		$this->settings = $settings;
		$this->db = $db;
	}

	/**
	 * @return array
	 */
	public function getSettings()
	{
		$settings = array(
			'core.default_ticket_dep'         => $this->settings->get('core.default_ticket_dep'),
			'core.phrase_department_singular' => $this->settings->get('core.phrase_department_singular'),
			'core.phrase_department_plural'   => $this->settings->get('core.phrase_department_plural'),
		);

		return $settings;
	}

	/**
	 * @param array $set_settings
	 * @throws \Exception
	 */
	public function setSettings(array $set_settings)
	{
		if (isset($set_settings['core.default_ticket_dep']) && $set_settings['core.default_ticket_dep'] != $this->settings->get('core.default_ticket_dep')) {
			$this->settings->setSetting('core.default_ticket_dep', $set_settings['core.default_ticket_dep']);
		}

		$change_phrase = array();
		if (isset($set_settings['core.phrase_department_singular']) && $set_settings['core.phrase_department_singular'] != $this->settings->get('core.phrase_department_singular')) {
			$change_phrase['singular'] = $set_settings['core.phrase_department_singular'];
		}
		if (isset($set_settings['core.phrase_department_plural']) && $set_settings['core.phrase_department_plural'] != $this->settings->get('core.phrase_department_plural')) {
			$change_phrase['plural'] = $set_settings['core.phrase_department_singular'];
		}

		if ($change_phrase) {
			if (!isset($change_phrase['singular'])) {
				$change_phrase['singular'] = $this->settings->get('core.phrase_department_singular');
			}
			if (!isset($change_phrase['plural'])) {
				$change_phrase['plural'] = $this->settings->get('core.phrase_department_plural');
			}

			$this->settings->setSetting('core.phrase_department_singular', $change_phrase['singular']);
			$this->settings->setSetting('core.phrase_department_plural', $change_phrase['plural']);

			$phrase_singular   = strtolower($change_phrase['singular']);
			$phrase_plural     = strtolower($change_phrase['plural']);
			$phrase_singular_c = ucwords($phrase_singular);
			$phrase_plural_c   = ucwords($phrase_plural);

			$groups_reader = new \Application\DeskPRO\ResourceScanner\LanguagePhrases();
			$phrases = $groups_reader->getAllUserPhrases();

			$batch = array();
			$ids = array();

			$d = date('Y-m-d H:i:s');

			foreach ($phrases as $phrase_id => $phrase_text) {
				$new_phrase = str_replace(
					array('departments', 'Departments', 'department', 'Department'),
					array($phrase_plural, $phrase_plural_c, $phrase_singular, $phrase_singular_c),
					$phrase_text
				);

				if ($new_phrase != $phrase_text) {
					$group = \Orb\Util\Strings::extractRegexMatch('#^(.*)\.([^.]+)$#', $phrase_id, 1);
					$batch[] = array(
						'language_id' => 1,
						'name'        => $phrase_id,
						'groupname'   => $group,
						'phrase'      => $new_phrase,
						'created_at'  => $d,
						'updated_at'  => $d
					);

					$ids[] = $phrase_id;
				}
			}

			if ($ids) {
				$this->db->beginTransaction();
				try {
					$this->db->executeQuery("
						DELETE FROM phrases
						WHERE name IN (" . $this->db->quoteIn($ids) . ") AND language_id = 1
					");

					$this->db->batchInsert('phrases', $batch);
					$this->db->commit();
				} catch (\Exception $e) {
					$this->db->rollback();
					throw $e;
				}
			}
		}
	}
}