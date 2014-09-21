<?php

/* * ************************************************************************\
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
  \************************************************************************* */

/**
 * @package Importer
 */

namespace Application\ImportBundle\ValueImporter;

use Application\ImportBundle\Value\CustomDefValue;
use Application\DeskPRO\DependencyInjection\DeskproContainer;
use Application\ImportBundle\RecordMapper\RecordMapperRegistry;
use Psr\Log\LoggerInterface;

abstract class AbstractCustomDefValueImporter extends AbstractValueImporter
{
	/** @var string */
	protected $data_table;
	/** @var string */
	protected $mapped_column;
	/** @var mixed */
	protected $mapped_value;
	/** @var string */
	protected $mapper_class;
	/** @var array */
	protected $custom_fields_array = array();
	/** @var array */
	protected $supported_custom_field_types = array(
		'Application\DeskPRO\CustomFields\Handler\Text',
		'Application\DeskPRO\CustomFields\Handler\Choice',
		'Application\DeskPRO\CustomFields\Handler\Textarea',
		'Application\DeskPRO\CustomFields\Handler\Toggle',
		'Application\DeskPRO\CustomFields\Handler\Date'
	);

	public function __construct($mode, DeskproContainer $container, LoggerInterface $logger, RecordMapperRegistry $mappers, $mapped_value)
	{
		parent::__construct($mode, $container, $logger, $mappers);

		$this->mapped_value = $mapped_value;
	}

	protected function isCustomFieldTypeSupported($field_type)
	{
		return in_array($field_type, $this->supported_custom_field_types);
	}

	protected function processCustomField(CustomDefValue $custom_def_value)
	{
		$record = array(
			$this->mapped_column => $this->mapped_value,
		);

		$field_key = $custom_def_value->key;

		$mapped_field_id = $this->getMapper()->findIdFromValue($field_key);

		if ($mapped_field_id && $this->isCustomFieldTypeSupported($this->getMapper()->fetchHandlerClass($field_key))) {
			$handler_class = $this->getMapper()->fetchHandlerClass($field_key);

			$custom_field_value = $custom_def_value->value;

			switch ($handler_class) {
				case 'Application\DeskPRO\CustomFields\Handler\Text':
				case 'Application\DeskPRO\CustomFields\Handler\Textarea':

					$record['field_id'] = $mapped_field_id;
					$record['root_field_id'] = $mapped_field_id;
					$record['input'] = $custom_def_value->value;

					break;
				case 'Application\DeskPRO\CustomFields\Handler\Toggle':

					$record['field_id'] = $mapped_field_id;
					$record['root_field_id'] = $mapped_field_id;
					$record['value'] = $custom_field_value ? 1 : 0;

					break;
				case 'Application\DeskPRO\CustomFields\Handler\Date':

					$record['field_id'] = $mapped_field_id;
					$record['root_field_id'] = $mapped_field_id;
					$record['value'] = $custom_field_value ? strtotime($custom_field_value) : 0;

					break;

				case 'Application\DeskPRO\CustomFields\Handler\Choice':

					// This is the choice value, now lets grab the choice id
					$custom_field_value_id = $this->getMappers()->findIdFromMappedValue('custom_def_ticket', $custom_field_value);

					if (!$custom_field_value_id) {
						$this->getLogger()->warning(sprintf("[%s] Unknown option %s for custom field %s (skipping)", $log_id, $custom_field_value, $field_key));
						break;
					} else {
						// TODO - Add the choice to the db
					}

					$record['field_id'] = $custom_field_value_id;
					$record['root_field_id'] = $mapped_field_id;
					$record['value'] = 1;

					break;
			}
			if (!empty($record)) {
				$this->getDb()->insert($this->data_table, $record);
			}
		}
	}

	protected function getMapper()
	{
		return $this->getMappers()->getMapper($this->mapper_class);
	}

}
