<?php
/**************************************************************************\
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
\**************************************************************************/

/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage Brand
 */

namespace Application\LanguageBundle\Language;

use Application\DeskPRO\Entity\Language;

/**
 * The LanguageStack is a way of managing changes in the "active" Language during runtime. It works similar to a stack
 * to allow
 * pushing into and popping out of language contexts during runtime. However, it keeps an internal state of the constructed
 * Languages so that each Language only need be created once during a single request, even if you pop in
 * and out of different languages multiple times.
 *
 * This allows us to operate in the context of a given language and quickly revert back to the old language context
 * without caring about how that is done.
 *
 * Ex. use in a service that depends on this stack
 *    public function mailMarketingPromo(Language $aUsersLanguage)
 *    {
 *         $this->languageStack->push($aUsersLanguage)
 *         $language = $this->brandStack->getActive()
 *         $this->languageStack->pop() // revert the stack so that our service doesn't interrupt others
 *     }
 *
 * Any service / controller that wants to work with a language should simply depend on this
 * LanguageStack and use getActive(). Pop in an out of different languages as necessary.
 */
class LanguageStack
{
	/**
	 * @var \array
	 */
	private $stack;

	/**
	 * @var Language[] an array of constructed languages keyed by language entity id
	 */
	private $languages;

	/**
	 * @var \Application\DeskPRO\Entity\Language
	 */
	private $default_language;


	public function __construct(Language $default)
	{
		$this->stack = array();
		$this->languages = array();
		$this->default_language = $default;
	}

	/**
	 * Gives you the active BrandContainer
	 *
	 * @return Language
	 */
	public function getActive()
	{
		$language_id = end($this->stack);

		if (false !== $language_id) {
			return $this->languages[$language_id];
		}

		return null;
	}


	public function getStack()
	{
		return $this->stack;
	}

	/**
	 * Pushes the Brand into the stack, so that the language's container is now active
	 *
	 * @param Language $language
	 * @return Language
	 */
	public function push(Language $language)
	{
		$language_id = $language->getId();

		array_push($this->stack, $language_id);

		if (!array_key_exists($language_id, $this->languages)) {
			$this->languages[$language_id] = $language;
		}

		return $this->getActive();
	}


	/**
	 * Reverts pops the state, making the previous language container active.
	 */
	public function pop()
	{
		array_pop($this->stack);
	}


	public function getDefault()
	{
		return $this->default_language;
	}
}
 