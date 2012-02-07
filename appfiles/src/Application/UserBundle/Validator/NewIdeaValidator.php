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
	protected $newfeedback;

	/**
	 * Check $value to see if its valid.
	 *
	 * @param \Application\DeskPRO\Ideas\NewIdea $newfeedback
	 * @return bool
	 */
	protected function checkIsValid($newfeedback)
	{
		$this->newfeedback = $newfeedback;

		$validator = new \Orb\Validator\StringLength(array('min' => 5));
		if (!$validator->isValid($this->newfeedback->title)) {
			$this->addError('title.short');
		}

		$validator = new \Orb\Validator\StringLength(array('min' => 10));
		if (!$validator->isValid($this->newfeedback->content)) {
			$this->addError('content.short');
		}

		$validator = new \Orb\Validator\StringLength(array('min' => 2));
		if (!$validator->isValid($this->newfeedback->person_name)) {
			$this->addError('person_name.short');
		}

		$cat = App::getEntityRepository('DeskPRO:FeedbackCategory')->find($this->newfeedback->category_id);
		if (!$cat) {
			$this->addError('category_id.invalid');
		}

		$person_context = $this->newfeedback->getPersonContext();
		if (!$person_context || $person_context->isGuest()) {
			$validator = new \Orb\Validator\StringEmail();
			if (!$validator->isValid($this->newfeedback->person_email)) {
				$this->addError('person_email.invalid');
			}
		}

		if ($this->errors) {
			return false;
		}

		return true;
	}
}
