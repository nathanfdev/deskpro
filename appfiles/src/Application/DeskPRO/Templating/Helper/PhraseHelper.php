<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @category Templating
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\DeskPRO\Templating\Helper;

use \Symfony\Component\Templating\Helper\Helper;
use \Application\DeskPRO\Translate\Translate;

class PhraseHelper extends Helper
{
    protected $tr;

    /**
	 * @param Translate $tr
	 */
    public function __construct(Translate $tr)
    {
        $this->tr = $tr;
    }

    /**
     * Generates a phrase.
     *
     * @param  string  $name       The name of the phrase
     * @param  array   $parameters An array of replacement parameters
     *
     * @return string The resulting phrase
     */
    public function phrase($name, array $parameters = array())
    {
		if (!$parameters) {
			return $this->tr->getPhraseText($name);
		}

		array_unshift($parameters, $name);
		return call_user_func_array(array($this->tr, 'phrase'), $parameters);
    }

    /**
     * Returns the canonical name of this helper.
     *
     * @return string The canonical name
     */
    public function getName()
    {
        return 'phrase';
    }
}
