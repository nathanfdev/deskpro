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

namespace Application\DeskPRO\ServerMysqlSortOrder;

use Application\DeskPRO\Settings\Settings;

class ServerMysqlSortOrder
{
	/**
	 * @var \Application\DeskPRO\Settings\Settings
	 */

	private $settings;

	public $db_collation = 'utf8_general_ci';
	public $db_collation_change = '';

	/**
	 * @param \Application\DeskPRO\Settings\Settings $settings
	 */

	public function __construct(Settings $settings)
	{
		$this->settings = $settings;
		$this->reset();
	}

	/**
	 * Resets based on stored values.
	 */

	public function reset()
	{
		$this->db_collation           = $this->settings->get('core.db_collation');
		$this->db_collation_change    = $this->settings->get('core.db_collation_change');
	}

	/**
	 * @return array
	 */

	public function toArray()
	{
		$export_settings = array();

		foreach (
			array(
				'db_collation'
			) as $s) {

			$export_settings[$s] = $this->$s;
		}

		return $export_settings;
	}

	/**
	 * @param array $new_values
	 */

	public function setArray(array $new_values)
	{
		foreach ($new_values as $v => $val) {
			if (property_exists($this, $v)) {
				$this->$v = $val;
			}
		}
	}

	/**
	 * Persists new values
	 */

	public function save()
	{
		if ($this->isCollationValid($this->db_collation)) {

			// notice this is 'db_collation_change', not 'db_collation'

			$this->settings->setSetting('core.db_collation_change', $this->db_collation);
		}
	}

	/**
	 * @return array
	 */

	public function getCollationsTable()
	{
		$collations = array(
			'utf8_general_ci'          => 'General Purpose (Default)',
			'utf8_unicode_ci'          => 'Unicode Default',
			'utf8_icelandic_ci'        => 'Icelandic',
			'utf8_latvian_ci'          => 'Latvian',
			'utf8_romanian_ci'         => 'Romanian',
			'utf8_slovenian_ci'        => 'Slovenian',
			'utf8_polish_ci'           => 'Polish',
			'utf8_estonian_ci'         => 'Estonian',
			'utf8_spanish_ci'          => 'Spanish',
			'utf8_spanish2_ci'         => 'Spanish (alternative)',
			'utf8_swedish_ci'          => 'Swedish',
			'utf8_turkish_ci'          => 'Turkish',
			'utf8_czech_ci'            => 'Czech',
			'utf8_danish_ci'           => 'Danish',
			'utf8_lithuanian_ci'       => 'Lithuanian',
			'utf8_slovak_ci'           => 'Slovak',
			'utf8_roman_ci'            => 'Latin',
			'utf8_persian_ci'          => 'Persian',
			'utf8_esperanto_ci'        => 'Esperanto',
			'utf8_hungarian_ci'        => 'Hungarian',
			'utf8_sinhala_ci'          => 'Sinhalese',
			'utf8_general_mysql500_ci' => 'General Purpose (MySQL 5.0)'
		);

		natcasesort($collations);

		return $collations;
	}

	/**
	 * @param $collation
	 *
	 * @return bool
	 */

	public function isCollationValid($collation)
	{
		$collations = $this->getCollationsTable();

		return isset($collations[$collation]);
	}

	/**
	 * @return array
	 */

	public function getUpdateStatus()
	{
		$status    = 'completed';
		$data      = null;
		$collation = null;

		if ($this->settings->get('core.db_collation_change')) {

			$status    = 'pending';
			$collation = $this->settings->get('core.db_collation_change');
		}

		if (file_exists(dp_get_tmp_dir() . '/db-collation-status.txt')) {

			$line = @file_get_contents(dp_get_tmp_dir() . '/db-collation-status.txt');

			if ($line && preg_match('/^\[(\d+)\|([a-z0-9_]+)]([a-z0-9_]+):(.*)$/si', $line, $match)) {

				$status    = $match[3];
				$collation = $match[2];
				$data      = array(
					'time'    => $match[1],
					'message' => $match[4]
				);
			}
		}

		switch ($status) {

			case 'pending':
				$message = 'Waiting to start...';
				break;

			case 'progress':
				$message = 'Converting table ' . $data['message'] . '...';
				break;

			case 'error':
				$message = 'An error occurred: ' . $data['message'];
				break;

			default:
				$message = 'Completed!';
				break;
		}

		return array(
			'status'    => $status,
			'collation' => $collation,
			'data'      => $data,
			'message'   => $message,
		);
	}
}