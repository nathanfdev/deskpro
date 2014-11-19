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
 * @subpackage
 */

namespace Application\LanguageBundle\Twig;

use Application\LanguageBundle\Language\LanguageManager;

class LanguageExtension extends \Twig_Extension
{
    /**
     * @var LanguageManager
     */
    private $language_manager;

    /**
     * @param LanguageManager $language_manager
     */
    public function __construct(LanguageManager $language_manager)
    {
        $this->language_manager = $language_manager;
    }

    /**
     * @return array
     */
    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction(
                'phrase',
                array($this, 'getPhrase'),
                array(
                    'is_safe'           => array('html'),
                    'needs_context'     => true,
                    'needs_environment' => true
                )
            )
        );
    }

    /**
     * @param \Twig_Environment $env
     * @param array             $context
     * @param string            $phrase_name
     * @param array             $vars
     * @param bool              $raw
     * @return mixed
     * @throws \Twig_Error_Runtime
     */
    public function getPhrase(\Twig_Environment $env, $context, $phrase_name, $vars = null, $raw = false)
    {
        if ($vars === null || !is_array($vars)) {
            $vars = array();
        }

        if (!$raw) {
            foreach ($vars as &$v) {
                $v = twig_escape_filter($env, $v, 'html');
            }
        }

        $vars['_context'] = $context;

        return $this->language_manager->getTranslator()->phrase($phrase_name, $vars);
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'phrase_extension';
    }
}
