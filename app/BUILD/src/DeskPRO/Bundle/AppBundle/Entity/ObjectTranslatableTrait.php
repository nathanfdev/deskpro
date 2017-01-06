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

/**
 * DeskPRO.
 */

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
    public function getObjectPropTranslations($prop_name)
    {
        $filtered = $this
            ->props_translations
            ->matching(new Criteria(Criteria::expr()->eq('propName', $prop_name)))
        ;

        return new ArrayCollection($filtered->getValues());
    }

    /**
     * {@inheritdoc}
     */
    public function getObjectPropLanguageTranslation($prop_name, Language $language = null)
    {
        $translations = $this->getObjectPropTranslations($prop_name);

        if ($language) {
            // look by language id
            foreach ($translations as $translation) {
                if ($translation->getLanguage() === $language) {
                    return $translation;
                }
            }

            // look by lang code
            foreach ($translations as $translation) {
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
    public function getObjectPropLanguageTranslationValue($prop_name, Language $language = null)
    {
        $translation = $this->getObjectPropLanguageTranslation($prop_name, $language);

        return $translation ? $translation->getValue() : '';
    }
}
