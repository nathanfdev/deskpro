<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @category Search
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\DeskPRO\Search\Indexer;

/**
 * A document represents something that we'll insert into the index.
 */
interface DocumentInterface
{
	/**
	 * Get the unique ID for this document in the index
	 *
	 * @return mixed
	 */
	public function getId();

	/**
	 * Get the type of document
	 *
	 * @return string
	 */
	public function getContentTypeName();

	/**
	 * Get the data to index
	 *
	 * @return array
	 */
	public function getData();

	/**
	 * Get the original object
	 *
	 * @return mixed
	 */
	public function getObject();
}