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

use DeskPRO\Bundle\AppBundle\Settings\Model\AbstractTranslationModel;
use JMS\Serializer\Annotation as JMS;

/**
 * Class WidgetBrandChatPopupTranslation.
 */
class WidgetBrandChatPopupTranslation extends AbstractTranslationModel
{
    /**
     * Translation title.
     *
     * @var string
     *
     * @JMS\Type("string")
     */
    private $title = 'Customer Support';

    /**
     * Translation message.
     *
     * @var string
     *
     * @JMS\Type("string")
     */
    private $message = 'Need help? Just reply to start a live chat with one of our team.';

    /**
     * Translation title.
     *
     * @var string
     *
     * @JMS\Type("string")
     */
    private $heading = 'Ask us a question!';

    /**
     * Translation message.
     *
     * @var string
     *
     * @JMS\Type("string")
     */
    private $subheading = 'Our team are online and ready to help with your enquiries. Send us a message to get started.';

    /**
     * Message at start button.
     *
     * @var string
     *
     * @JMS\Type("string")
     */
    private $startButton = 'Start a conversation';

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
    public function getHeading()
    {
        return $this->heading;
    }

    /**
     * @param string $heading
     *
     * @return $this
     */
    public function setHeading($heading)
    {
        $this->heading = $heading;

        return $this;
    }

    /**
     * @return string
     */
    public function getSubheading()
    {
        return $this->subheading;
    }

    /**
     * @param string $subheading
     *
     * @return $this
     */
    public function setSubheading($subheading)
    {
        $this->subheading = $subheading;

        return $this;
    }

    /**
     * @return string
     */
    public function getStartButton()
    {
        return $this->startButton;
    }

    /**
     * @param string $startButton
     *
     * @return $this
     */
    public function setStartButton($startButton)
    {
        $this->startButton = $startButton;

        return $this;
    }
}
