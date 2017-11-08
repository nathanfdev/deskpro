<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2017, DeskPRO Ltd.
 *
 * The license agreement under which this software is released
 * can be found at https://www.deskpro.com/eula/
 *
 * By using this software, you acknowledge having read the license
 * and agree to be bound thereby.
 *
 * Please note that DeskPRO is not free software. We release the full
 * source code for our software because we trust our users to pay us for
 * the huge investment in time and energy that has gone into both creating
 * this software and supporting our customers. By providing the source code
 * we preserve our customers' ability to modify, audit and learn from our
 * work. We have been developing DeskPRO since 2001, please help us make it
 * another decade.
 *
 * Like the work you see? Think you could make it better? We are always
 * looking for great developers to join us: http://www.deskpro.com/jobs/
 *
 * ~ Thanks, Everyone at Team DeskPRO
 */

namespace DeskPRO\Bundle\AppBundle\Language;

use Application\DeskPRO\Entity\Language;
use Application\DeskPRO\Translate\Translate;
use Doctrine\ORM\EntityManager;

/**
 * Class LanguageManager.
 */
class LanguageManager
{
    /**
     * @var Translate
     */
    private $translate;

    /**
     * @var LanguageStack
     */
    private $languageStack;

    /**
     * @var EntityManager
     */
    private $em;

    /**
     * @var bool
     */
    private $multiLanguage;

    /**
     * @var Language
     */
    private $callLanguage;

    /**
     * Constructor.
     *
     * @param Translate     $translate
     * @param LanguageStack $languageStack
     * @param EntityManager $em
     */
    public function __construct(Translate $translate, LanguageStack $languageStack, EntityManager $em)
    {
        $this->translate     = $translate;
        $this->languageStack = $languageStack;
        $this->em            = $em;
        $this->multiLanguage = null;
    }

    /**
     * @return bool
     */
    public function isMultiLanguagePortal()
    {
        if ($this->multiLanguage !== null) {
            return $this->multiLanguage;
        }

        return $this->multiLanguage = ($this->em->getRepository(Language::class)->countPortalLanguages() > 1);
    }

    /**
     * @param string $lang_code an arbitrary lang string
     *
     * @return bool
     */
    public function isLanguageSupported($lang_code)
    {
        $lang_code = $this->normalizeLanguageCode($lang_code);

        return $this->getLanguage($lang_code) !== null;
    }

    /**
     * @param string $lang_code an arbitrary lang string
     *
     * @return Language
     */
    public function getLanguage($lang_code)
    {
        $lang_code = $this->normalizeLanguageCode($lang_code);

        return $this->em->getRepository(Language::class)->getForLangCode($lang_code);
    }

    /**
     * @param string $sys_name the system id for a language (default, french, etc)
     *
     * @return Language
     */
    public function getLanguageBySystemName($sys_name)
    {
        return $this->em->getRepository(Language::class)->findOneBy(['sys_name' => $sys_name]);
    }

    /**
     * Turns an arbitrary lang string into a more normalized lang string that we use internally for URLs.
     *
     * @param string $lang_code
     *
     * @return string
     */
    public function normalizeLanguageCode($lang_code)
    {
        return $lang_code;
    }

    /**
     * @return LanguageStack
     */
    public function getLanguageStack()
    {
        return $this->languageStack;
    }

    /**
     * @return array
     */
    public function getEnabledLanguages()
    {
        return $this->em->getRepository(Language::class)->getPortalLanguages();
    }

    /**
     * Get a DeskPRO Translate object ready to be used with the currently active Lang.
     *
     * @param Language|string|null $lang
     *
     * @return Translate
     */
    public function getTranslator($lang = null)
    {
        if ($this->callLanguage) {
            $lang = $this->callLanguage;
        } elseif (!$lang) {
            if (!$lang = $this->languageStack->getActive()) {
                $lang = $this->languageStack->getDefaultLanguage();
            }
        } elseif (!$lang instanceof Language) {
            $lang = $this->getLanguage($lang);
        }

        $this->translate->setLanguage($lang);

        return $this->translate;
    }

    /**
     * Shortcut method to use the existing brand stack language to fetch a phrase from the Translator.
     *
     * Optionally, you can provide the $lang to use.
     *
     * @param $name
     * @param array         $vars
     * @param Language|null $lang not necessary, will use currently active lang if null
     *
     * @return string
     */
    public function phrase($name, array $vars = [], Language $lang = null)
    {
        return $this->getTranslator($lang)->phrase($name, $vars);
    }

    /**
     * @param $object
     * @param null          $property
     * @param Language|null $lang     not necessary, will use currently active lang if null
     *
     * @return string
     */
    public function objectPhrase($object, $property = null, Language $lang = null)
    {
        return $this->getTranslator($lang)->getPhraseObject($object, $property);
    }

    /**
     * Call a function with the language set ot $language. After the call,
     * the language is reset back to the default.
     *
     * @param Language $language
     * @param callable $func
     *
     * @return mixed
     */
    public function callWithLanguage(Language $language = null, $func)
    {
        $this->callLanguage = $language;

        try {
            return $this->translate->callWithLanguage($this->callLanguage, $func);
        } finally {
            $this->callLanguage = null;
        }
    }
}
