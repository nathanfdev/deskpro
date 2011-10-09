<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage UserBundle
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\UserBundle\Validator;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity;

use Orb\Util\Arrays;
use Orb\Validator\AbstractValidator;

class NewTicketReplyValidator extends AbstractValidator
{
	protected $run_validators = array();

	/**
	 * @var \Application\UserBundle\Tickets\NewReply
	 */
	protected $newreply;

	/**
	 * Check $value to see if its valid.
	 *
	 * @param \Application\UserBundle\Tickets\NewReply $newreply
	 * @return bool
	 */
	protected function checkIsValid($newreply)
	{
		$this->newreply = $newreply;

		$validator = new \Orb\Validator\StringLength(array('min' => 5));
		if (!$validator->isValid($this->newreply->message)) {
			$this->addError('message.short');
		}

		if ($this->errors) {
			return false;
		}

		return true;
	}

	protected function _traverseItems(array $items)
	{
		foreach ($items as $item) {
			if ($item['item_type'] == 'group') {
				if (empty($item['items'])) {
					continue;
				}

				$this->_traverseItems($item['items']);
			} else {
				$this->_validateItem($item);
			}
		}
	}
}
