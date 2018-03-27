<?php

namespace DeskPRO\Bundle\AppBundle\Settings\Model\Widget\Options\BrandSettings\ButtonSettings;

use DeskPRO\Bundle\AppBundle\Settings\Model\AbstractTranslationModel;
use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class WidgetBrandButtonTranslation.
 */
class WidgetBrandButtonTranslation extends AbstractTranslationModel
{
    /**
     * Chat button name.
     *
     * @var string
     *
     * @JMS\Type("string")
     * @Assert\NotBlank()
     */
    private $name;

    /**
     * Ticket fallback button name.
     *
     * @var string
     *
     * @JMS\Type("string")
     * @Assert\NotBlank()
     */
    private $contactUs;

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     *
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return string
     */
    public function getContactUs()
    {
        return $this->contactUs;
    }

    /**
     * @param string $contactUs
     *
     * @return $this
     */
    public function setContactUs($contactUs)
    {
        $this->contactUs = $contactUs;

        return $this;
    }
}
