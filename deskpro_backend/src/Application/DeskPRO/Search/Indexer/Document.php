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
class Document implements DocumentInterface
{
	/**
	 * @var int
	 */
	protected $id;

	/**
	 * @var string
	 */
	protected $content_type;

	/**
	 * Data array of properties
	 * @var array
	 */
	protected $data;

	
	/**
	 * @param array $info
	 * @return \Application\DeskPRO\Search\Indexer\Document
	 */
	public static function newFromArray(array $info)
	{
		$id = $info['id'];
		$content_type = $info['content_type'];

		unset($info['id'], $info['content_type']);

		return new self($id, $content_type, $info);
	}


	public function __construct($id, $content_type, array $data = array())
	{
		$this->id           = $id;
		$this->content_type = $content_type;
		$this->data         = $data;
	}

	/**
	 * Get the unique ID for this document in the index
	 *
	 * @return mixed
	 */
	public function getId()
	{
		return $this->id;
	}


	/**
	 * Get the type of document
	 *
	 * @return string
	 */
	public function getContentTypeName()
	{
		return $this->content_type;
	}

	
	/**
	 * Get the data to index
	 *
	 * @return array
	 */
	public function getData()
	{
		return $this->data;
	}
}