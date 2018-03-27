<?php

namespace DeskPRO\Bundle\PortalBundle\Model;

use Application\DeskPRO\Entity\Language;
use JMS\Serializer\Annotation as JMS;

/**
 * Class WidgetPhrases.
 */
class WidgetPhrases
{
    /**
     * @JMS\Type("array")
     *
     * @var array
     */
    private $phrases = [];

    /**
     * @JMS\Type("string")
     *
     * @var string
     */
    private $direction = 'LTR';

    /**
     * Constructor.
     *
     * @param array    $phrases
     * @param Language $language
     */
    public function __construct(array $phrases, Language $language = null)
    {
        $this->phrases = $phrases;
        if ($language) {
            $this->direction = $language->getDirection();
        }
    }
}
