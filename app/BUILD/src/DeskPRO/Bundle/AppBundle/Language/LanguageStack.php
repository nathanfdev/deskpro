<?php

namespace DeskPRO\Bundle\AppBundle\Language;

use Application\DeskPRO\Entity\Language;
use Application\DeskPRO\NewSettings\SettingsResolver;
use Application\DeskPRO\Translate\SystemLanguage;
use Doctrine\ORM\EntityManager;

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
     * @var array
     * @var Language[] an array of constructed languages keyed by language entity id
     */
    private $languages;

    /**
     * @var SettingsResolver
     */
    private $settingsResolver;

    /**
     * @var EntityManager
     */
    private $em;

    /**
     * only stores the reference for quick access in the case of multiple calls to getDefaultLanguage(), do not
     * use this prop directly.
     *
     * @var \Application\DeskPRO\Entity\Language|null
     */
    private $defaultLanguage;

    /**
     * Constructor.
     *
     * @param SettingsResolver $settingsResolver
     * @param EntityManager    $em
     */
    public function __construct(SettingsResolver $settingsResolver, EntityManager $em)
    {
        $this->stack            = [];
        $this->languages        = [];
        $this->settingsResolver = $settingsResolver;
        $this->em               = $em;
    }

    /**
     * Gives you the active BrandContainer.
     *
     * @return Language
     */
    public function getActive()
    {
        $language_id = end($this->stack);

        if (false !== $language_id) {
            return $this->languages[$language_id];
        }

        return;
    }

    /**
     * Get the active languge from the stack. If the stack is empty, return the default.
     *
     * @return Language
     */
    public function getActiveOrDefault()
    {
        return $this->getActive() ?: $this->getDefaultLanguage();
    }

    /**
     * @return array
     */
    public function getStack()
    {
        return $this->stack;
    }

    /**
     * Pushes the Brand into the stack, so that the language's container is now active.
     *
     * @param Language $language
     *
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
     * A common use case is to switch to the default language (routing for ex.) quickly. This is a convenience method.
     *
     * @return Language
     */
    public function pushDefault()
    {
        return $this->push($this->getDefaultLanguage());
    }

    /**
     * Reverts pops the state, making the previous language container active.
     */
    public function pop()
    {
        array_pop($this->stack);
    }

    /**
     * Can find the default system language. You should use the stack directly, but if nothing is on the stack
     * and you need to find the set default language, use this.
     *
     * @return Language
     */
    public function getDefaultLanguage()
    {
        if ($this->defaultLanguage) {
            return $this->defaultLanguage;
        }

        $lang = null;
        if ($id = $this->settingsResolver->getGlobalSettings()->get('core.default_language_id')) {
            if ($lang = $this->em->getRepository(Language::class)->find($id)) {
                return $this->defaultLanguage = $lang;
            }
        }

        if ($lang = $this->em->getRepository(Language::class)->find(1)) {
            return $this->defaultLanguage = $lang;
        }

        return SystemLanguage::getInstance();
    }
}
