<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2016, DeskPRO Ltd.
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

namespace DeskPRO\Bundle\AppBundle\Settings\Model\Widget\Options\BrandSettings\ChatSettings;

use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class WidgetBrandChatPopupSettings.
 */
class WidgetBrandChatPopupSettings
{
    const REPLY_TYPE_BUTTONS = 'buttons';
    const REPLY_TYPE_BUTTON  = 'reply';

    /**
     * @var string
     *
     * @JMS\Type("string")
     */
    private $title = 'DeskPRO Customer Support';

    /**
     * @var string
     *
     * @JMS\Type("string")
     */
    private $message = 'Given a string consisting of printable ASCII chars, produce an output consisting of its unique chars in the original order.';

    /**
     * @var string
     *
     * @JMS\Type("string")
     * @Assert\NotBlank()
     */
    private $replyType = self::REPLY_TYPE_BUTTONS;

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param string $title
     *
     * @return $this
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @param string $message
     *
     * @return $this
     */
    public function setMessage($message)
    {
        $this->message = $message;

        return $this;
    }

    /**
     * @return string
     */
    public function getReplyType()
    {
        return $this->replyType;
    }

    /**
     * @param string $replyType
     *
     * @return $this
     */
    public function setReplyType($replyType)
    {
        $this->replyType = $replyType;

        return $this;
    }
}
