<?php

namespace DeskPRO\Bundle\AppBundle\Entity;

use Application\DeskPRO\Entity\Language;
use Application\DeskPRO\Entity\ObjectLang;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Selectable;

/**
 * Uses to work with ObjectLang entities.
 * Add this interface to an entity to enable ObjectTranslatableListener callbacks.
 *
 * Interface ObjectTranslatableInterface.
 */
interface ObjectTranslatableInterface extends EntityInterface
{
    /**
     * Returns object ref in format like entity_name.{id}.
     *
     * @return string
     */
    public function getObjectRef();

    /**
     * Set a collection of entity translations.
     *
     * @param Collection|ObjectLang[] $collection
     */
    public function setObjectPropsTranslations(Collection $collection);

    /**
     * Returns a collection of entity translations.
     *
     * @return Collection|Selectable|ObjectLang[]
     */
    public function getObjectPropsTranslations();

    /**
     * Returns a collection of entity translations filtered by property name.
     *
     * @param string $propName
     *
     * @return Collection|ObjectLang[]
     */
    public function getObjectPropTranslations($propName);

    /**
     * Returns language specific translation.
     *
     * @param string   $propName
     * @param Language $language
     *
     * @return ObjectLang|null
     */
    public function getObjectPropLanguageTranslation($propName, Language $language = null);

    /**
     * Returns language specific translation value.
     *
     * @param string        $propName
     * @param Language|null $language
     *
     * @return string
     */
    public function getObjectPropLanguageTranslationValue($propName, Language $language = null);
}
