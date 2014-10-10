<?php
/**************************************************************************\
 * | DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/  |
 * | a British company located in London, England.                            |
 * |                                                                          |
 * | All source code and content Copyright (c) 2014, DeskPRO Ltd.             |
 * |                                                                          |
 * | The license agreement under which this software is released              |
 * | can be found at https://www.deskpro.com/eula/                            |
 * |                                                                          |
 * | By using this software, you acknowledge having read the license          |
 * | and agree to be bound thereby.                                           |
 * |                                                                          |
 * | Please note that DeskPRO is not free software. We release the full       |
 * | source code for our software because we trust our users to pay us for    |
 * | the huge investment in time and energy that has gone into both creating  |
 * | this software and supporting our customers. By providing the source code |
 * | we preserve our customers' ability to modify, audit and learn from our   |
 * | work. We have been developing DeskPRO since 2001, please help us make it |
 * | another decade.                                                          |
 * |                                                                          |
 * | Like the work you see? Think you could make it better? We are always     |
 * | looking for great developers to join us: http://www.deskpro.com/jobs/    |
 * |                                                                          |
 * | ~ Thanks, Everyone at Team DeskPRO                                       |
 * \**************************************************************************/

/**
 * Orb
 *
 * @package    Orb
 * @subpackage Doctrine
 */

namespace Orb\Doctrine\DBAL\Driver\PDOODBC;

class Connection extends \Doctrine\DBAL\Driver\PDOConnection implements \Doctrine\DBAL\Driver\Connection
{
	protected $_pdoTransactionsSupport = null;
	protected $_pdoLastInsertIdSupport = null;


	/**
	 * @override
	 */
	public function quote($value, $type = \PDO::PARAM_STR)
	{
		$val = parent::quote($value, $type);

		// Some PDO drivers dont implement quote(), so we need to do ourselves
		// This is a rther dumb 'escape' where we just remove bad chars and then quote
		if (!$val && $value) {
			if(is_numeric($value)) {
				$val = $value;
			} else {
				if ($value === null) return 'NULL';
				if ($value === "") return '';
				if ($value === true) return 1;
				if ($value === false) return 0;
				if (is_numeric($value)) return $value;

				$non_displayables = array(
					'/%0[0-8bcef]/',            // url encoded 00-08, 11, 12, 14, 15
					'/%1[0-9a-f]/',             // url encoded 16-31
					'/[\x00-\x08]/',            // 00-08
					'/\x0b/',                   // 11
					'/\x0c/',                   // 12
					'/[\x0e-\x1f]/'             // 14-31
				);
				foreach ($non_displayables as $regex) {
					$value = preg_replace($regex, '', $value);
				}

				$value = str_replace("'", "''", $value );
				return "'" . $value . "'";
			}
		}

		return $val;
	}


	/**
	 * @return bool transaction support
	 */
	private function _pdoTransactionsSupported()
	{
		if (!is_null($this->_pdoTransactionsSupport)) {
			return $this->_pdoTransactionsSupport;
		}

		try {
			$supported = true;
			parent::beginTransaction();
		} catch (\PDOException $e) {
			$supported = false;
		}
		if ($supported) {
			parent::commit();
		}

		return $this->_pdoTransactionsSupport = $supported;
	}


	/**
	 * {@inheritdoc}
	 */
	public function rollback()
	{
		if ($this->_pdoTransactionsSupported() === true) {
			parent::rollback();
		} else {
			$this->exec('ROLLBACK TRANSACTION');
		}
	}


	/**
	 * {@inheritdoc}
	 */
	public function commit()
	{
		if ($this->_pdoTransactionsSupported() === true) {
			parent::commit();
		} else {
			$this->exec('COMMIT TRANSACTION');
		}
	}


	/**
	 * {@inheritdoc}
	 */
	public function beginTransaction()
	{
		if ($this->_pdoTransactionsSupported() === true) {
			parent::beginTransaction();
		} else {
			$this->exec('BEGIN TRANSACTION');
		}
	}


	/**
	 * @return bool lastInsertId support
	 */
	private function _pdoLastInsertId()
	{
		if (!is_null($this->_pdoLastInsertIdSupport)) {
			return $this->_pdoLastInsertIdSupport;
		}

		try {
			$supported = true;
			parent::lastInsertId();
		} catch (\PDOException $e) {
			$supported = false;
		}

		return $this->_pdoLastInsertIdSupport = $supported;
	}


	/**
	 * {@inheritdoc}
	 */
	public function lastInsertId($name = null)
	{
		$id = null;
		if ($this->_pdoLastInsertId() === true) {
			$id = parent::lastInsertId();
		} else {
			$stmt = $this->query('SELECT SCOPE_IDENTITY()');
			$id   = $stmt->fetchColumn();
			$stmt->closeCursor();
		}

		return $id;
	}
}