<?php

namespace DeskPRO\Bundle\AppBundle\Serializer\Model\AgentAlerts;

use DeskPRO\Component\Util\RegexUtils;
use JMS\Serializer\Annotation as JMS;
use Orb\Util\Strings;

/**
 * Class NotifyData.
 */
class NotifyData
{
    /**
     * Notification title.
     *
     * @JMS\Type("string")
     *
     * @var string
     */
    private $title;
    /**
     * Notification summary.
     *
     * @JMS\Type("string")
     *
     * @var string
     */
    private $summary;

    /**
     * NotifyData constructor.
     *
     * @param string $input
     */
    public function __construct($input)
    {
        $title       = Strings::extractRegexMatch('#<big>(.*?)</big>#s', $input);
        $title       = preg_replace('#<span[^>]*>.*?</span>#s', '', $title);
        $this->title = $this->cleanString($title);

        $summary       = Strings::extractRegexMatch('#<small>(.*?)</small>#s', $input);
        $this->summary = $this->cleanString($summary);
    }

    /**
     * @param string $string
     *
     * @return string
     */
    private function cleanString($string)
    {
        $string = Strings::decodeHtmlEntities($string);
        $string = Strings::removeInvisibleCharacters($string);
        $string = Strings::removeLineBreaks($string);
        $string = str_replace("\t", ' ', $string);
        $string = RegexUtils::safePregReplace('#\s{,2}#', ' ', $string);
        $string = trim($string);

        return $string;
    }
}
