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
