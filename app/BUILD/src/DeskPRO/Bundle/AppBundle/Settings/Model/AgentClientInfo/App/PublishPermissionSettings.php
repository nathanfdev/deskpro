<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2018, DeskPRO Ltd.
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
 * Class PublishPermissionSettings.
 */
class PublishPermissionSettings
{
    /**
     * @JMS\Type("boolean")
     *
     * @var bool
     */
    private $use;

    /**
     * @JMS\Type("boolean")
     *
     * @var bool
     */
    private $create;

    /**
     * @JMS\Type("boolean")
     *
     * @var bool
     */
    private $delete;

    /**
     * @JMS\Type("boolean")
     *
     * @var bool
     */
    private $edit;

    /**
     * @JMS\Type("boolean")
     *
     * @var bool
     */
    private $canInsertHtml;

    /**
     * @JMS\Type("boolean")
     *
     * @var bool
     */
    private $validate;

    /**
     * @return bool
     */
    public function isUse()
    {
        return $this->use;
    }

    /**
     * @param bool $use
     *
     * @return $this
     */
    public function setUse($use)
    {
        $this->use = $use;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getCreate()
    {
        return $this->create;
    }

    /**
     * @param mixed $create
     *
     * @return $this
     */
    public function setCreate($create)
    {
        $this->create = $create;

        return $this;
    }

    /**
     * @return bool
     */
    public function isDelete()
    {
        return $this->delete;
    }

    /**
     * @param bool $delete
     *
     * @return $this
     */
    public function setDelete($delete)
    {
        $this->delete = $delete;

        return $this;
    }

    /**
     * @return bool
     */
    public function isEdit()
    {
        return $this->edit;
    }

    /**
     * @param bool $edit
     *
     * @return $this
     */
    public function setEdit($edit)
    {
        $this->edit = $edit;

        return $this;
    }

    /**
     * @return bool
     */
    public function isCanInsertHtml()
    {
        return $this->canInsertHtml;
    }

    /**
     * @param bool $canInsertHtml
     *
     * @return $this
     */
    public function setCanInsertHtml($canInsertHtml)
    {
        $this->canInsertHtml = $canInsertHtml;

        return $this;
    }

    /**
     * @return bool
     */
    public function isValidate()
    {
        return $this->validate;
    }

    /**
     * @param bool $validate
     *
     * @return $this
     */
    public function setValidate($validate)
    {
        $this->validate = $validate;

        return $this;
    }
}
