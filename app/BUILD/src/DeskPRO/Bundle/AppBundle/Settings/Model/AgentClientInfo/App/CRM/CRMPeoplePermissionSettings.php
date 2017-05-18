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

namespace DeskPRO\Bundle\AppBundle\Settings\Model\AgentClientInfo\App\CRM;

use JMS\Serializer\Annotation as JMS;

/**
 * Class CRMPeoplePermissionSettings.
 */
class CRMPeoplePermissionSettings
{
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
    private $edit;

    /**
     * @JMS\Type("boolean")
     *
     * @var bool
     */
    private $validate;

    /**
     * @JMS\Type("boolean")
     *
     * @var bool
     */
    private $manageEmails;

    /**
     * @JMS\Type("boolean")
     *
     * @var bool
     */
    private $resetPassword;

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
    private $notes;

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
    private $disable;

    /**
     * @JMS\Type("boolean")
     *
     * @var bool
     */
    private $loginAs;

    /**
     * @JMS\Type("boolean")
     *
     * @var bool
     */
    private $merge;

    /**
     * @return bool
     */
    public function isCreate()
    {
        return $this->create;
    }

    /**
     * @param bool $create
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

    /**
     * @return bool
     */
    public function isManageEmails()
    {
        return $this->manageEmails;
    }

    /**
     * @param bool $manageEmails
     *
     * @return $this
     */
    public function setManageEmails($manageEmails)
    {
        $this->manageEmails = $manageEmails;

        return $this;
    }

    /**
     * @return bool
     */
    public function isResetPassword()
    {
        return $this->resetPassword;
    }

    /**
     * @param bool $resetPassword
     *
     * @return $this
     */
    public function setResetPassword($resetPassword)
    {
        $this->resetPassword = $resetPassword;

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
     * @return bool
     */
    public function isNotes()
    {
        return $this->notes;
    }

    /**
     * @param bool $notes
     *
     * @return $this
     */
    public function setNotes($notes)
    {
        $this->notes = $notes;

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
    public function isDisable()
    {
        return $this->disable;
    }

    /**
     * @param bool $disable
     *
     * @return $this
     */
    public function setDisable($disable)
    {
        $this->disable = $disable;

        return $this;
    }

    /**
     * @return bool
     */
    public function isLoginAs()
    {
        return $this->loginAs;
    }

    /**
     * @param bool $loginAs
     *
     * @return $this
     */
    public function setLoginAs($loginAs)
    {
        $this->loginAs = $loginAs;

        return $this;
    }

    /**
     * @return bool
     */
    public function isMerge()
    {
        return $this->merge;
    }

    /**
     * @param bool $merge
     *
     * @return $this
     */
    public function setMerge($merge)
    {
        $this->merge = $merge;

        return $this;
    }
}
