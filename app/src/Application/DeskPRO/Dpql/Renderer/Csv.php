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

/**
 * Renders DPQL results to CSV.
 */
class Csv extends AbstractRenderer
{
	/**
	 * Gets the MIME content type for this type of output.
	 *
	 * @return string
	 */
	public function getContentType()
	{
		return 'text/csv';
	}

	/**
	 * Gets the file extension for this type of output.
	 *
	 * @return string
	 */
	public function getExtension()
	{
		return 'csv';
	}

	/**
	 * Joins the already rendered tables into one output.
	 *
	 * @param array $tables
	 *
	 * @return string
	 */
	protected function _implodeSplitTables(array $tables)
	{
		return implode("\r\n\r\n", $tables);
	}

	/**
	 * Finalizes the rendering of a split table by rendering the body with the header.
	 *
	 * @param string $header
	 * @param string $table
	 *
	 * @return string
	 */
	protected function _renderSplitTableWithHeader($header, $table)
	{
		return "\"$header\"\r\n$table";
	}

	/**
	 * Renders a table with the specified rows/data.
	 *
	 * @param array $rows
	 *
	 * @return string
	 */
	protected function _renderTable(array $rows)
	{
		if (!$rows) {
			return '';
		}

		if ($this->_handler->getGroupXColumns()) {
			return $this->_renderMatrixTable($rows);
		}

		$groupXColumns = $this->_handler->getGroupXColumns();
		$groupYColumns = $this->_handler->getGroupYColumns();
		$selectColumns = $this->_handler->getSelectColumns();

		$output = array();

		$columns = array();
		foreach ($groupXColumns AS $column) {
			$columns[] = $this->wrapCell($column['title']);
		}
		foreach ($groupYColumns AS $column) {
			$columns[] = $this->wrapCell($column['title']);
		}
		foreach ($selectColumns AS $column) {
			$columns[] = $this->wrapCell($column['title']);
		}

		$output[] = implode(',', $columns);

		foreach ($rows AS $row) {
			$columns = array();
			foreach ($groupXColumns AS $column) {
				$columns[] = $this->wrapCell($this->_renderCellValue($row, $column));
			}
			foreach ($groupYColumns AS $column) {
				$columns[] = $this->wrapCell($this->_renderCellValue($row, $column));
			}
			foreach ($selectColumns AS $column) {
				$columns[] = $this->wrapCell($this->_renderCellValue($row, $column));
			}

			$output[] = implode(',', $columns);
		}

		return implode("\r\n", $output);
	}

	/**
	 * Renders a matrix table (with X and Y grouping).
	 *
	 * @param array $rows
	 *
	 * @return string
	 */
	protected function _renderMatrixTable(array $rows)
	{
		$prepared = $this->_prepareMatrixTable($rows);
		$lookup = $prepared['lookup'];

		$rows = array();

		$headerCols = $this->_getFinalMatrixPathsWithPrintable(array('root'), $prepared['xDistinct']);

		$headerRow = array();
		if ($this->_handler->getGroupYColumns()) {
			$headerRow[] = $this->wrapCell('');
		}
		$headerRow = array_merge($headerRow, $headerCols);
		$rows[] = implode(',', $headerRow);

		$rowGroups = $this->_getFinalMatrixPathsWithPrintable(array('root'), $prepared['yDistinct']);
		if (!$rowGroups) {
			// need to fake it so we get a row with no Y grouping
			$rowGroups = array('root' => '');
		}

		foreach ($rowGroups AS $yPath => $printable) {
			$columns = array();

			foreach ($headerCols AS $xPath => $null) {
				if (isset($lookup[$yPath][$xPath])) {
					$value = $lookup[$yPath][$xPath];
				} else {
					$value = '';
				}
				$columns[] = $this->wrapCell($value);
			}

			if ($printable != '') {
				$printable .= ',';
			}
			$rows[] = $printable . implode(',', $columns);
		}

		return implode("\r\n", $rows);
	}

	/**
	 * Gets the final paths to a matrix row/column entry with the value being the printable
	 * value that lead to that entry.
	 *
	 * @param array $path
	 * @param array $distinctValues
	 * @param array $printPath
	 *
	 * @return array
	 */
	protected function _getFinalMatrixPathsWithPrintable(array $path, array $distinctValues, array $printPath = array())
	{
		$pathLookup = $this->_getGroupPathKey($path);
		if (!isset($distinctValues[$pathLookup])) {
			return array();
		}

		$output = array();

		foreach ($distinctValues[$pathLookup] AS $key => $value) {
			$localPath = $path;
			$localPath[] = $key;

			$localPrintPath = $printPath;
			$localPrintPath[] = $value;

			$childOutput = $this->_getFinalMatrixPathsWithPrintable($localPath, $distinctValues, $localPrintPath);
			if (!$childOutput) {
				// a leaf - responsible for output
				$output[$this->_getGroupPathKey($localPath)] = $this->wrapCell(implode(' / ', $localPrintPath));
			} else {
				$output = array_merge($output, $childOutput);
			}
		}

		return $output;
	}

	/**
	 * Renders a null value.
	 *
	 * @return string
	 */
	protected function _renderNull()
	{
		return '';
	}

	/**
	 * Renders a boolean value.
	 *
	 * @param boolean $value
	 *
	 * @return string
	 */
	protected function _renderBoolean($value)
	{
		return ($value ? 'Y' : 'N');
	}

	/**
	 * Escapes the value for direct output.
	 *
	 * @param string $value
	 *
	 * @return string
	 */
	public function escapeValue($value)
	{
		return $value;
	}

	/**
	 * Wraps the value as an entire cell.
	 *
	 * @param string $value
	 *
	 * @return string
	 */
	public function wrapCell($value)
	{
		$value = str_replace('"', '""', $value);
		return "\"$value\"";
	}
}