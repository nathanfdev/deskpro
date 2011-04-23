<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage Elastica
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\DeskPRO\Elastica\Type;

use Application\DeskPRO\Elastica\ElasticaManager;

/**
 * A type represents a type of indexable document
 */
abstract class AbstractType
{
	/**
	 * @var \Application\DeskPRO\Elastica\ElasticaManager
	 */
	protected $manager;

	public function __construct(ElasticaManager $manager)
	{
		$this->manager = $manager;
	}



	/**
	 * Transform a value into a Document
	 *
	 * This is like using a Transformer, but this should also set the proper Index
	 * and Type names.
	 *
	 * @param  $value
	 * @return \Elastic_Document
	 */
	abstract public function transformToDocument($value);


	/**
	 * Transform a documents into this type.
	 *
	 * $value can be a single value, or an array of values
	 *
	 * @param \Elastic_Document|Elastic_Document[] $docs
	 * @return mixed
	 */
	public function transformToType($docs)
	{
		if (is_array($docs)) {
			if (!$docs) return array();
			return $this->getValuesFromDocs($docs);
		} else {
			if (!$docs) return null;
			return $this->getValueFromDoc($docs);
		}
	}


	/**
	 * Get a single value from a document
	 *
	 * @return mixed
	 */
	abstract protected function getValueFromDoc(\Elastica_Document $doc);

	
	/**
	 * Get multiple values from documents
	 *
	 * @return array
	 */
	protected function getValuesFromDocs(array $docs)
	{
		$values = array();
		foreach ($docs as $doc) {
			$v = $this->getValueFromDoc($doc);
			if ($v) {
				$values[] = $v;
			}
		}
		return $values;
	}
}