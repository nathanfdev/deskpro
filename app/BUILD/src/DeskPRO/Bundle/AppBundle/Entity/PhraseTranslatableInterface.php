<?php

namespace DeskPRO\Bundle\AppBundle\Entity;

use Application\DeskPRO\Entity\Language;
use Application\DeskPRO\Entity\Phrase;
use Application\DeskPRO\Translate\HasPhraseName;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Selectable;

/**
 * Interface PhraseTranslatableInterface.
 */
interface PhraseTranslatableInterface extends EntityInterface, HasPhraseName
{
    /**
     * Set a collection of entity prop translations.
     *
     * @param Collection|Phrase[] $collection
     */
    public function setPhraseTranslations(Collection $collection);

    /**
     * Returns a collection of entity prop translations.
     *
     * @return Collection|Selectable|Phrase[]
     */
    public function getPhraseTranslations();

    /**
     * @param string $propName
     *
     * @return Collection|Selectable|Phrase[]
     */
    public function getPhrasePropTranslations($propName);

    /**
     * @param string   $propName
     * @param Language $language
     *
     * @return Phrase|null
     */
    public function getPhrasePropTranslation($propName, Language $language);
}
