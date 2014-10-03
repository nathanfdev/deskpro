<?php

/* * ************************************************************************\
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
  \************************************************************************* */

/**
 * @package Importer
 */

namespace Application\ImportBundle\ValueImporter;

use Application\ImportBundle\Value\CustomDefValue;
use Application\ImportBundle\Exception\BadDataException;
use Application\ImportBundle\Exception\DuplicateValueException;

class CustomDefTicketValueImporter extends AbstractCustomDefValueImporter
{

	protected $mapped_column = 'ticket_id';
	protected $mapper_class = 'custom_def_ticket';
	protected $data_table = 'custom_data_ticket';

	/**
	 * Imports the Custom Field value
	 * 
	 * @param \Application\ImportBundle\Value\CustomDefValue $custom_value
	 * @return boolean
	 * @throws \InvalidArgumentException
	 * @throws BadDataException
	 * @throws DuplicateValueException
	 */
	public function importValue($custom_value)
	{
		if (!($custom_value instanceof CustomDefValue)) {
			throw new \InvalidArgumentException("This importer can only import Ticket Custom Fields");
		}

		$log_id = "Ticket CustomField :: " . $custom_value->oid . " ";

		if (!$custom_value->key) {
			throw new BadDataException(sprintf('[%s] Custom field data must have a key', $log_id));
		}

		return $this->processCustomField($custom_value);
	}

}