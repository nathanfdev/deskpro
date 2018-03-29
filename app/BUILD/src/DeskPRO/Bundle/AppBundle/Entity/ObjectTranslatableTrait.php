<?php

namespace DeskPRO\Bundle\AppBundle\Entity;

use Application\DeskPRO\Entity\Language;
use Application\DeskPRO\Entity\ObjectLang;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\Collections\Selectable;

/**
 * Helper to work with ObjectLang entities.
 *
 * @property Collection|Selectable|ObjectLang[] $props_translations
 */
trait ObjectTranslatableTrait
{
    /**
     * {@inheritdoc}
     */
    public function getObjectPropsTranslations()
    {
        return $this->props_translations;
    }

    /**
     * {@inheritdoc}
     */
    public function setObjectPropsTranslations(Collection $collection)
    {
        $this->props_translations = $collection;
    }

    /**
     * {@inheritdoc}
     *
     * @return ArrayCollection|ObjectLang[]
     */
    public function getObjectPropTranslations($propName)
    {
        $filtered = $this
            ->props_translations
            ->matching(new Criteria(Criteria::expr()->eq('propName', $propName)))
        ;

        return new ArrayCollection($filtered->getValues());
    }

    /**
     * {@inheritdoc}
     */
    public function getObjectPropLanguageTranslation($propName, Language $language = null)
    {
        $translations = $this->getObjectPropTranslations($propName);

        if ($language) {
            // look by language id
            foreach ($translations as $translation) {
                if ($translation->getLanguage() === $language) {
                    return $translation;
                }
            }

            // look by lang code
            foreach ($translations as $translation) {
                if (!$translation->getLanguage()) {
                    continue;
                }
                if ($translation->getLanguage()->getLangCode() === $language->getLangCode()) {
                    return $translation;
                }
            }
        }

        // if nothing found then return first one available
        return $translations->first();
    }

    /**
     * {@inheritdoc}
     */
    public function getObjectPropLanguageTranslationValue($propName, Language $language = null)
    {
        $translation = $this->getObjectPropLanguageTranslation($propName, $language);

        return $translation ? $translation->getValue() : '';
    }
}
