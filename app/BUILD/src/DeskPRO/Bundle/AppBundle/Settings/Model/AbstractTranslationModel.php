<?php

namespace DeskPRO\Bundle\AppBundle\Settings\Model;

use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class AbstractTranslationModel.
 */
abstract class AbstractTranslationModel
{
    /**
     * Language.
     *
     * @var int
     *
     * @JMS\Type("integer")
     * @Assert\NotNull()
     */
    protected $language;

    /**
     * @return int
     */
    public function getLanguage()
    {
        return $this->language;
    }

    /**
     * @param int $language
     *
     * @return $this
     */
    public function setLanguage($language)
    {
        $this->language = $language;

        return $this;
    }
}
