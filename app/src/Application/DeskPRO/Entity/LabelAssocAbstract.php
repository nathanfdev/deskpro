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
 * @category Entities
 */

namespace Application\DeskPRO\Entity;

use Application\DeskPRO\App;

/**
 * Base labels associations class
 *
 */
abstract class LabelAssocAbstract extends \Application\DeskPRO\Domain\DomainObject
{
	/**
	 * The 'type' of label this is for, as it could be found in the
	 * LabelDef.
	 */
	const LABEL_TYPENAME = 'OVERRIDE';

	/**
	 * @var string
	 */
	protected $label;


	/**
	 * @param string $label
	 */
	public function setLabel($label)
	{
		$label = trim(strtolower($label));
		$label = str_replace(',', '', $label);
		$this->label = $label;
	}


	public function __toString()
	{
		return $this->label;
	}
}
