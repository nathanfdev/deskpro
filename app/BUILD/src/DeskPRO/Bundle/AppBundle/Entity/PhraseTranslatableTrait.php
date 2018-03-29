<?php

namespace DeskPRO\Bundle\AppBundle\Entity;

use Application\DeskPRO\Entity\Language;
use Application\DeskPRO\Entity\Phrase;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

/**
 * Class PhraseTranslatableTrait.
 *
 * @method getPhraseName($propName)
 */
trait PhraseTranslatableTrait
{
    /**
     * @var ArrayCollection|Phrase[]
     */
    protected $phraseTranslations;

    /**
     * {@inheritdoc}
     */
    public function setPhraseTranslations(Collection $collection)
    {
        $this->phraseTranslations = $collection;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getPhraseTranslations()
    {
        if (!$this->phraseTranslations) {
            $this->phraseTranslations = new ArrayCollection();
        }

        return $this->phraseTranslations;
    }

    /**
     * {@inheritdoc}
     */
    public function getPhrasePropTranslations($propName)
    {
        $phraseName = $this->getPhraseName($propName);

        return $this->getPhraseTranslations()->filter(function (Phrase $phrase) use ($phraseName) {
            return $phrase->getName() === $phraseName;
        });
    }

    /**
     * {@inheritdoc}
     */
    public function getPhrasePropTranslation($propName, Language $language)
    {
        return $this->getPhrasePropTranslations($propName)->filter(function (Phrase $phrase) use ($language) {
            return $phrase->getLanguage() === $language;
        })->first();
    }
}
