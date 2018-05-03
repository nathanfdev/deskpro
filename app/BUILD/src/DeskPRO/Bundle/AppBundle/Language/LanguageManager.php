<?php

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
     * @return Language[]
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
