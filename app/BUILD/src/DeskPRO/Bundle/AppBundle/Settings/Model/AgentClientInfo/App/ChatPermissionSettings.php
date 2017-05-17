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

namespace DeskPRO\Bundle\AppBundle\Settings\Model\AgentClientInfo\App;

use JMS\Serializer\Annotation as JMS;

/**
 * Class ChatPermissionSettings.
 */
class ChatPermissionSettings
{
    /**
     * @JMS\Type("boolean")
     *
     * @var bool
     */
    private $viewTranscripts;

    /**
     * @JMS\Type("boolean")
     *
     * @var bool
     */
    private $viewUnassigned;

    /**
     * @JMS\Type("boolean")
     *
     * @var bool
     */
    private $viewOthers;

    /**
     * @JMS\Type("boolean")
     *
     * @var bool
     */
    private $createLabels;

    /**
     * @JMS\Type("boolean")
     *
     * @var bool
     */
    private $delete;

    /**
     * @return bool
     */
    public function isViewTranscripts()
    {
        return $this->viewTranscripts;
    }

    /**
     * @param bool $viewTranscripts
     *
     * @return $this
     */
    public function setViewTranscripts($viewTranscripts)
    {
        $this->viewTranscripts = $viewTranscripts;

        return $this;
    }

    /**
     * @return bool
     */
    public function isViewUnassigned()
    {
        return $this->viewUnassigned;
    }

    /**
     * @param bool $viewUnassigned
     *
     * @return $this
     */
    public function setViewUnassigned($viewUnassigned)
    {
        $this->viewUnassigned = $viewUnassigned;

        return $this;
    }

    /**
     * @return bool
     */
    public function isViewOthers()
    {
        return $this->viewOthers;
    }

    /**
     * @param bool $viewOthers
     *
     * @return $this
     */
    public function setViewOthers($viewOthers)
    {
        $this->viewOthers = $viewOthers;

        return $this;
    }

    /**
     * @return bool
     */
    public function isCreateLabels()
    {
        return $this->createLabels;
    }

    /**
     * @param bool $createLabels
     *
     * @return $this
     */
    public function setCreateLabels($createLabels)
    {
        $this->createLabels = $createLabels;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getDelete()
    {
        return $this->delete;
    }

    /**
     * @param mixed $delete
     *
     * @return $this
     */
    public function setDelete($delete)
    {
        $this->delete = $delete;

        return $this;
    }
}
