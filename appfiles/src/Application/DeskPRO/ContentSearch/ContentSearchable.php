<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @category ContentSearch
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\DeskPRO\ContentSearch;

/**
 * If content is searchable, it should impleemnt this interface so the indexer can work with it
 */
interface ContentSearchable
{
	/**
	 * Get the unique ID for this item
	 */
	public function getSearchId();

	/**
	 * Get a normalized string that we should insert into the search database
	 *
	 * @return string
	 */
	public function getSearchContent();

	/**
	 * Get an array of k=>v pairs of additional content we sholud insert into the search database.
	 * 
	 * @return array
	 */
	public function getSearchAttributes();
}