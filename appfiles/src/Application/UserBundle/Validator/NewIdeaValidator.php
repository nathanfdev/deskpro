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

class NewIdeaValidator extends AbstractValidator
{
	/**
	 * @var \Application\DeskPRO\Ideas\NewIdea
	 */
	protected $newidea;

	/**
	 * Check $value to see if its valid.
	 *
	 * @param \Application\DeskPRO\Ideas\NewIdea $newidea
	 * @return bool
	 */
	protected function checkIsValid($newidea)
	{
		$this->newidea = $newidea;

		$validator = new \Orb\Validator\StringLength(array('min' => 5));
		if (!$validator->isValid($this->newidea->title)) {
			$this->addError('title.short');
		}

		$validator = new \Orb\Validator\StringLength(array('min' => 10));
		if (!$validator->isValid($this->newidea->content)) {
			$this->addError('content.short');
		}

		$validator = new \Orb\Validator\StringLength(array('min' => 2));
		if (!$validator->isValid($this->newidea->person_name)) {
			$this->addError('person_name.short');
		}

		$cat = App::getEntityRepository('DeskPRO:IdeaCategory')->find($this->newidea->category_id);
		if (!$cat) {
			$this->addError('category_id.invalid');
		}

		$person_context = $this->newidea->getPersonContext();
		if (!$person_context || $person_context->isGuest()) {
			$validator = new \Orb\Validator\StringEmail();
			if (!$validator->isValid($this->newidea->person_email)) {
				$this->addError('person_email.invalid');
			}
		}

		if ($this->errors) {
			return false;
		}

		return true;
	}
}
