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

use Application\DeskPRO\Translate\Translate;

/**
 * A delegate phrase object can be passed to the translator, and the translator will
 * call getPhraseText() on-demand. This allows you to pass around single objects that self-
 * contain all the info they need to render a phrase, without actually rendering the text.
 *
 * For example, if you need to pass in a phrase to a template that is rendered multiple times
 * for multiple languages, you can construct a single object and the object can render a different
 * phrase on-demand.
 */
interface DelegatePhraseInterface
{
	/**
	 * Get the phrase text.
	 * 
	 * @param  $translator
	 * @return string
	 */
	public function getPhrase(Translate $translator);
}