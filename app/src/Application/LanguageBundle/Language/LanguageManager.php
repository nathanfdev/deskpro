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

namespace Application\LanguageBundle\Language;

use Application\DeskPRO\Translate\Translate;
use Application\DeskPRO\Entity\Language;
use Application\DeskPRO\EntityRepository\Language as LanguageRepo;

class LanguageManager
{
    /**
     * @var Translate
     */
    private $translate;

    /**
     * @var LanguageStack
     */
    private $language_stack;

    /**
     * @var \Application\DeskPRO\EntityRepository\Language
     */
    private $language_repo;

    /**
     * @var bool
     */
    private $multi_langauge;

    public function __construct(Translate $translate, LanguageStack $language_stack, LanguageRepo $language_repo)
    {
        $this->translate = $translate;
        $this->language_stack = $language_stack;
        $this->language_repo = $language_repo;
        $this->multi_langauge = null;
    }

    /**
     * @return bool
     */
    public function isMultiLanguagePortal()
    {
        if ($this->multi_langauge !== null) {
            return $this->multi_langauge;
        }

        return $this->multi_langauge = ($this->language_repo->countPortalLanguages() > 1);
    }

    /**
     * @param  string $lang_code an arbitrary lang string
     * @return bool
     */
    public function isLanguageSupported($lang_code)
    {
        $lang_code = $this->normalizeLanguageCode($lang_code);

        return $this->getLanguage($lang_code) !== null;
    }

    /**
     * @param  string   $lang_code an arbitrary lang string
     * @return Language
     */
    public function getLanguage($lang_code)
    {
        $lang_code = $this->normalizeLanguageCode($lang_code);

        return $this->language_repo->getForLangCode($lang_code);
    }

    /**
     * Turns an arbitrary lang string into a more normalized lang string that we use internally for URLs.
     *
     * @param  string $lang_code
     * @return string
     */
    public function normalizeLanguageCode($lang_code)
    {
        return strtolower(substr($lang_code, 0, 2));
    }

    /**
     * @return LanguageStack
     */
    public function getLanguageStack()
    {
        return $this->language_stack;
    }

    /**
     * @return array
     */
    public function getEnabledLanguages()
    {
        return $this->language_repo->getPortalLanguages();
    }

    /**
     * TODO this is meant to return a translate object for a specific lang
     *
     * @param Language|string|null $lang
     * @return Translate
     */
    public function getTranslator($lang = null)
    {
        return $this->translate;
    }
}
