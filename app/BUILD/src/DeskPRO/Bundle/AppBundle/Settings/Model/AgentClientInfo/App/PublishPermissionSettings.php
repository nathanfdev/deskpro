<?php

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
