<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2015, DeskPRO Ltd.
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

namespace Application\ImportBundle\Entity;

use Symfony\Component\Validator\Constraints;
use Symfony\Component\Validator\Mapping\ClassMetadata;

/**
 * Class ObjectLang.
 */
final class ObjectLang extends AbstractEntity
{
    /**
     * @var string
     */
    private $language;

    /**
     * @var string
     */
    private $property;

    /**
     * @var string
     */
    private $value;

    /**
     * {@inheritdoc}
     */
    public function getType()
    {
        return self::TYPE_OBJECT_LANG;
    }

    /**
     * @return string
     */
    public function getLanguage()
    {
        return $this->language;
    }

    /**
     * @param string $language
     *
     * @return $this
     */
    public function setLanguage($language)
    {
        $this->language = $language;

        return $this;
    }

    /**
     * @return string
     */
    public function getProperty()
    {
        return $this->property;
    }

    /**
     * @param string $property
     *
     * @return $this
     */
    public function setProperty($property)
    {
        $this->property = $property;

        return $this;
    }

    /**
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param string $value
     *
     * @return $this
     */
    public function setValue($value)
    {
        $this->value = $value;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function toArray()
    {
        return array(
            'oid'      => $this->oid,
            'language' => $this->language,
            'property' => $this->property,
            'value'    => $this->value,
        );
    }

    /**
     * Filters duplicate translations.
     *
     * @param Collection $translations
     *
     * @return ObjectLang[]
     */
    public static function getUniqueCollection(Collection $translations)
    {
        $unique_entities = new Collection();
        $unique_keys     = array();

        foreach ($translations as $translation) {
            /* @var ObjectLang $translation */
            $unique_key = $translation->getLanguage().'_'.$translation->getProperty();

            if (!isset($unique_keys[$unique_key])) {
                $unique_keys[$unique_key] = 1;
                $unique_entities->attach($translation);
            }
        }

        return $unique_entities;
    }

    /**
     * {@inheritdoc}
     */
    public static function loadValidatorMetadata(ClassMetadata $metadata)
    {
        AbstractEntity::loadValidatorMetadata($metadata);

        $metadata
            ->addPropertyConstraint('language', new Constraints\NotBlank())
            ->addPropertyConstraint('property', new Constraints\NotBlank())
            ->addPropertyConstraint('value', new Constraints\NotBlank())
        ;
    }
}
