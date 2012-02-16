<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage Translate
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\DeskPRO\Translate;

use Application\DeskPRO\Entity\Language;

class DelegatePhrase implements DelegatePhraseInterface
{
	protected $phrase_name;
	protected $phrase_vars = array();

	public function __construct($phrase_name, array $phrase_vars = array())
	{
		$this->phrase_name = $phrase_name;
		$this->phrase_vars = $phrase_vars;
	}


	/**
	 * Get the phrase text.
	 *
	 * @param  $translator
	 * @return string
	 */
	public function getPhrase(Translate $translator, Language $language = null)
	{
		return $translator->phrase($this->phrase_name, $this->phrase_vars, $language);
	}


	/**
	 * @return string
	 */
	public function getPhraseName()
	{
		return $this->phrase_name;
	}


	/**
	 * @return array
	 */
	public function getPhraseVars()
	{
		return $this->phrase_vars;
	}


	public function __toString()
	{
		return $this->phrase_name;
	}
}
