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
 * @subpackage Dpql
 */

namespace Application\DeskPRO\Dpql\Renderer;

use Application\DeskPRO\Dpql\ResultHandler;
use Application\DeskPRO\Dpql\Results;
use Application\DeskPRO\App;

abstract class AbstractRenderer
{
	protected static $_rendererMap = array(
		'csv' => 'Csv',
		'html' => 'Html'
	);

	/**
	 * Result handler that stores all the bits that will be displayed/formatted.
	 *
	 * @var \Application\DeskPRO\Dpql\ResultHandler
	 */
	protected $_handler;

	/**
	 * Results to render.
	 *
	 * @var \Application\DeskPRO\Dpql\Results
	 */
	protected $_results;

	protected $_typeName = '';

	/**
	 * Internal handler used when rendering to count how many rows
	 * row spans need to be used for.
	 *
	 * @var array
	 */
	protected $_rowSpans = array();

	/**
	 * Internal handler used when rendering to determine which row groups
	 * have been "hit" and printed.
	 *
	 * @var array
	 */
	protected $_rowGroupHit = array();

	/**
	 * Gets the MIME content type for this type of output.
	 *
	 * @return string
	 */
	abstract public function getContentType();

	/**
	 * Gets the file extension for this type of output.
	 *
	 * @return string
	 */
	abstract public function getExtension();

	/**
	 * Joins the already rendered tables into one output.
	 *
	 * @param array $tables
	 *
	 * @return string
	 */
	abstract protected function _implodeSplitTables(array $tables);

	/**
	 * Finalizes the rendering of a split table by rendering the body with the header.
	 *
	 * @param string $header
	 * @param string $table
	 *
	 * @return string
	 */
	abstract protected function _renderSplitTableWithHeader($header, $table);

	/**
	 * Renders a table with the specified rows/data.
	 *
	 * @param array $rows
	 *
	 * @return string
	 */
	abstract protected function _renderTable(array $rows);


	/**
	 * Renders a null value.
	 *
	 * @return string
	 */
	abstract protected function _renderNull();

	/**
	 * Renders a boolean value.
	 *
	 * @param boolean $value
	 *
	 * @return string
	 */
	abstract protected function _renderBoolean($value);

	/**
	 * Escapes the value for direct output.
	 *
	 * @param string $value
	 *
	 * @return string
	 */
	abstract public function escapeValue($value);

	/**
	 * @param string $typeName
	 * @param \Application\DeskPRO\Dpql\ResultHandler $resultHandler
	 * @param \Application\DeskPRO\Dpql\Results $results
	 */
	protected function __construct($typeName, ResultHandler $resultHandler, Results $results)
	{
		$this->_typeName = $typeName;
		$this->_handler = $resultHandler;
		$this->_results = $results;
	}

	public function getFileName($name)
	{
		$name = preg_replace('/[^a-zA-Z0-9_ -]/', '', $name);
		$name = str_replace(' ', '-', $name);

		return strtolower($name) . '.' . $this->getExtension();
	}

	/**
	 * Render to the specified format
	 *
	 * @return string
	 */
	public function render()
	{
		$splitColumns = $this->_handler->getSplitColumns();

		if ($splitColumns) {
			$output = array();
			foreach ($this->_results->getSplitResults() AS $splitResult) {
				$table = $this->_renderSplitTable($splitResult);
				if ($table) {
					$output[] = $table;
				}
			}

			return $this->_implodeSplitTables($output);
		} else {
			return $this->_renderTable($this->_results->getResults());
		}
	}

	/**
	 * Renders results with a SPLIT clause into however many tables
	 * are needed.
	 *
	 * @param array $splitResult Key 0 is rows in table, 1 is columns in split query
	 *
	 * @return string
	 */
	protected function _renderSplitTable(array $splitResult)
	{
		$table = $this->_renderTable($splitResult[0]);
		if (!$table) {
			return '';
		}

		$splitPrint = array();
		foreach ($this->_handler->getSplitColumns() AS $splitColumn) {
			$splitPrint[] = $this->_renderCellValue($splitResult[1], $splitColumn);
		}

		return $this->_renderSplitTableWithHeader(implode(' / ', $splitPrint), $table);
	}

	/**
	 * Renders the value for a specific cell.
	 *
	 * @param mixed[int] $row
	 * @param array $column
	 *
	 * @return string
	 */
	protected function _renderCellValue(array $row, array $column)
	{
		$renderer = $column['renderer'];
		$value = $column['resultId'] ? $row[$column['resultId'] - 1] : '';

		return $this->renderValue($value, $renderer);
	}

	/**
	 * Renders a value, ready to be output. Note that the value may still needed
	 * to be wrapped to be valid (quotes, td html tag, etc).
	 *
	 * @param string $value
	 * @param string|\Closure $format
	 * 
	 * @return string
	 */
	public function renderValue($value, $format)
	{
		if ($renderer instanceof \Closure) {
			return $renderer($this->_typeName, $value, $row, $this);
		}

		if ($value === null) {
			return $this->_renderNull();
		}

		switch ($format) {
			case 'boolean':
				return $this->_renderBoolean($value);

			case 'datetime':
			case 'date':
			case 'time':
				$settingMap = array(
					'datetime' => 'core.date_fulltime',
					'date' => 'core.date_full',
					'time' => 'core.date_time'
				);

				$tz = App::getCurrentPerson()->getTimezone();
				try {
					$date = new \DateTime($value, new \DateTimeZone($tz));
					return $this->escapeValue($date->format(App::getSetting($settingMap[$format])));
				} catch (\Exception $e) {
					return $this->escapeValue($value);
				}

			case 'string':
			default:
				return $this->escapeValue($value);
		}
	}

	/**
	 * Gets the value of a particular column for the given row.
	 *
	 * @param array $row
	 * @param integer $id
	 *
	 * @return string
	 */
	protected function _getColumnValue(array $row, $id)
	{
		return ($id ? $row[$id - 1] : '');
	}

	/**
	 * Prepares data for a matrix table.
	 *
	 * Returns array with:
	 *  - xDistinct[pathString][renderedValue] = true -- used to find distinct values over X grouping
	 *  - yDistinct[pathString][renderedValue] = true -- used to find distinct values over Y grouping
	 *  - lookup[yPath][xPath] = cell value -- value for cell at the y/x position specified
	 *
	 * @param array $rows
	 *
	 * @return array
	 */
	protected function _prepareMatrixTable(array $rows)
	{
		$groupXColumns = $this->_handler->getGroupXColumns();
		$groupYColumns = $this->_handler->getGroupYColumns();
		$selectColumns = $this->_handler->getSelectColumns();

		$distinctXValues = array();
		$distinctYValues = array();
		$lookup = array();

		foreach ($rows AS $row) {
			$xPath = array('root');
			foreach ($groupXColumns AS $column) {
				$pathString = $this->_getGroupPathKey($xPath);
				$groupValue = $this->_getColumnValue($row, $column['groupResultId']);

				$distinctXValues[$pathString][$groupValue] = $this->_renderCellValue($row, $column);

				$xPath[] = $groupValue;
			}

			$yPath = array('root');
			foreach ($groupYColumns AS $column) {
				$pathString = $this->_getGroupPathKey($yPath);
				$groupValue = $this->_getColumnValue($row, $column['groupResultId']);

				$distinctYValues[$pathString][$groupValue] = $this->_renderCellValue($row, $column);

				$yPath[] = $groupValue;
			}

			$lookup[$this->_getGroupPathKey($yPath)][$this->_getGroupPathKey($xPath)] =
				$this->_renderMatrixCell($row, $selectColumns);
		}

		return array(
			'xDistinct' => $distinctXValues,
			'yDistinct' => $distinctYValues,
			'lookup' => $lookup
		);
	}

	/**
	 * Gets the final path keys to a set of distinct values in a matrix table.
	 *
	 * @param array $path Grouping paths
	 * @param array $distinct Distinct values
	 *
	 * @return array List of path keys
	 */
	protected function _getFinalMatrixPaths(array $path, array $distinct)
	{
		$pathString = $this->_getGroupPathKey($path);
		if (!isset($distinct[$pathString])) {
			return array();
		}

		$output = array();
		foreach ($distinct[$pathString] AS $value => $null) {
			$localPath = $path;
			$localPath[] = $value;

			$children = $this->_getFinalMatrixPaths($localPath, $distinct);
			if (!$children) {
				$output[] = $this->_getGroupPathKey($localPath);
			} else {
				$output = array_merge($output, $children);
			}
		}

		return $output;
	}

	/**
	 * Renders a matrix cell.
	 *
	 * @param array $row
	 * @param array $selectColumns
	 *
	 * @return string
	 */
	protected function _renderMatrixCell(array $row, array $selectColumns)
	{
		$values = array();
		foreach ($selectColumns AS $column) {
			$values[] = $this->_renderCellValue($row, $column);
		}

		return implode(' / ', $values);
	}

	/**
	 * Gets the string key to identify a path to a value based on parts.
	 *
	 * @param array $groupParts
	 *
	 * @return string
	 */
	protected function _getGroupPathKey(array $groupParts)
	{
		return implode('|', $groupParts);
	}

	public static function create($type, ResultHandler $resultHandler, Results $results)
	{
		$type = strtolower($type);
		if (!isset(self::$_rendererMap[$type])) {
			throw new \Exception("Invalid DPQL renderer type $type");
		}

		$class = __NAMESPACE__ . '\\' . self::$_rendererMap[$type];
		return new $class($type, $resultHandler, $results);
	}
}