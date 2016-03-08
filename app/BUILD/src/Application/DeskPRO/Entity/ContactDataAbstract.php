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

/**
 * DeskPRO.
 *
 * @category Entities
 */
namespace Application\DeskPRO\Entity;

use Application\DeskPRO\ContactData\ContactData;
use JMS\Serializer\Annotation as JMS;

/**
 * Contact data is stuff like address, instant messaging, phone etc.
 * These can be applied to People and Organizations.
 *
 * Because of the nature, each 'data_type' uses each of the field1-field10
 * differently. Sometimes only a single one might be used, other times multiple.
 *
 * @JMS\ExclusionPolicy("ALL")
 */
abstract class ContactDataAbstract extends \Application\DeskPRO\Domain\DomainObject
{
    /**
     * The unique ID.
     *
     * @var int
     *
     * @JMS\Expose()
     * @JMS\Type("integer")
     */
    protected $id = null;

    /**
     * The handler class.
     *
     * @var string
     *
     * @JMS\Expose()
     * @JMS\Type("string")
     */
    protected $contact_type;

    /**
     * The label/comment/name for this contact entry (Work, Home, etc).
     *
     * @var string
     */
    protected $comment = '';

    /**
     * @var string
     */
    protected $field_1 = '';

    /**
     * @var string
     */
    protected $field_2 = '';

    /**
     * @var string
     */
    protected $field_3 = '';

    /**
     * @var string
     */
    protected $field_4 = '';

    /**
     * @var string
     */
    protected $field_5 = '';

    /**
     * @var string
     */
    protected $field_6 = '';

    /**
     * @var string
     */
    protected $field_7 = '';

    /**
     * @var string
     */
    protected $field_8 = '';

    /**
     * @var string
     */
    protected $field_9 = '';

    /**
     * @var string
     */
    protected $field_10 = '';

    /**
     * Instance of the handler class.
     *
     * @var \Application\DeskPRO\ContactData\AbstractContactData
     */
    protected $_handler = null;

    /**
     * @var array
     */
    protected $_save_callbacks = array();

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getContactType()
    {
        return $this->contact_type;
    }

    /**
     * Set a contact type.
     *
     * @param string $contact_type
     *
     * @return $this
     */
    public function setContactType($contact_type)
    {
        $this->setModelField('contact_type', (string) $contact_type);

        return $this;
    }

    /**
     * Set a comment.
     *
     * @param $comment
     *
     * @return $this
     */
    public function setComment($comment = '')
    {
        $this->setModelField('comment', (string) $comment);

        return $this;
    }

    /**
     * @return string
     */
    public function getComment()
    {
        return $this->comment;
    }

    /**
     * @param string $field_1
     *
     * @return $this
     */
    public function setField1($field_1 = '')
    {
        $this->setModelField('field_1', (string) $field_1);

        return $this;
    }

    /**
     * @return string
     */
    public function getField1()
    {
        return $this->field_1;
    }

    /**
     * @param string $field_2
     *
     * @return $this
     */
    public function setField2($field_2 = '')
    {
        $this->setModelField('field_2', (string) $field_2);

        return $this;
    }

    /**
     * @return string
     */
    public function getField2()
    {
        return $this->field_2;
    }

    /**
     * @param string $field_3
     *
     * @return $this
     */
    public function setField3($field_3 = '')
    {
        $this->setModelField('field_3', (string) $field_3);

        return $this;
    }

    /**
     * @return string
     */
    public function getField3()
    {
        return $this->field_3;
    }

    /**
     * @param string $field_4
     *
     * @return $this
     */
    public function setField4($field_4 = '')
    {
        $this->setModelField('field_4', (string) $field_4);

        return $this;
    }

    /**
     * @return string
     */
    public function getField4()
    {
        return $this->field_4;
    }

    /**
     * @param string $field_5
     *
     * @return $this
     */
    public function setField5($field_5 = '')
    {
        $this->setModelField('field_5', (string) $field_5);

        return $this;
    }

    /**
     * @return string
     */
    public function getField5()
    {
        return $this->field_5;
    }

    /**
     * @param string $field_6
     *
     * @return $this
     */
    public function setField6($field_6 = '')
    {
        $this->setModelField('field_6', (string) $field_6);

        return $this;
    }

    /**
     * @return string
     */
    public function getField6()
    {
        return $this->field_6;
    }

    /**
     * @param string $field_7
     *
     * @return $this
     */
    public function setField7($field_7 = '')
    {
        $this->setModelField('field_7', (string) $field_7);

        return $this;
    }

    /**
     * @return string
     */
    public function getField7()
    {
        return $this->field_7;
    }

    /**
     * @param string $field_8
     *
     * @return $this
     */
    public function setField8($field_8 = '')
    {
        $this->setModelField('field_8', (string) $field_8);

        return $this;
    }

    /**
     * @return string
     */
    public function getField8()
    {
        return $this->field_8;
    }

    /**
     * @param string $field_9
     *
     * @return $this
     */
    public function setField9($field_9 = '')
    {
        $this->setModelField('field_9', (string) $field_9);

        return $this;
    }

    /**
     * @return string
     */
    public function getField9()
    {
        return $this->field_9;
    }

    /**
     * @param string $field_10
     *
     * @return $this
     */
    public function setField10($field_10 = '')
    {
        $this->setModelField('field_10', (string) $field_10);

        return $this;
    }

    /**
     * @return string
     */
    public function getField10()
    {
        return $this->field_10;
    }

    /**
     * Get the DeskPRO form field object that knows how to render data etc.
     *
     * @return \Application\DeskPRO\ContactData\AbstractContactData
     */
    public function getHandler()
    {
        if ($this->_handler !== null) {
            return $this->_handler;
        }
        $this->_handler = ContactData::getHandler($this->contact_type);

        return $this->_handler;
    }

    /**
     * @param array $input
     */
    public function applyFormData(array $input)
    {
        $this->getHandler()->applyFormData($input, $this);
    }

    /**
     * Get values that will be useful in a template.
     *
     * @return string
     */
    public function getTemplateVars()
    {
        $vars                 = $this->getHandler()->getTemplateVars($this);
        $vars['contact_type'] = $this->getHandler()->getContactType();
        $vars['id']           = $this->id;
        $vars['rec']          = $this;

        return $vars;
    }

    /**
     * Gets a collapsed string that can be tried for searches.
     *
     * @return mixed
     */
    public function getSearchString($prevent = false)
    {
        $pieces = array();
        for ($i = 1; $i <= 10; ++$i) {
            $field = 'field_'.$i;
            if ($this->$field) {
                $pieces[] = $this->$field;
            }
        }

        $pieces = implode(',', $pieces);
        if (!$prevent) {
            $pieces = preg_replace('#\s#', '', $pieces);
            $pieces = \Orb\Util\Strings::utf8_strtolower($pieces);
        }

        return $pieces;
    }

    /**
     * @param $string
     *
     * @return bool
     */
    public function checkStringMatch($string)
    {
        $string = preg_replace('#\s#', '', $string);
        $string = \Orb\Util\Strings::utf8_strtolower($string);

        return strpos($this->getSearchString(), $string) !== false;
    }

    public function addSaveCallback(\Closure $callback)
    {
        $this->_save_callbacks[] = $callback;
    }

    public function _preSave()
    {
        foreach ($this->_save_callbacks as $callback) {
            $callback($this);
        }
    }

    public function _preDelete()
    {
        $this->getHandler()->deleteType($this);
    }

    public function toApiData($primary = true, $deep = true, array $visited = array())
    {
        $data = parent::toApiData($primary, $deep, $visited);
        $data = array_merge($data, $this->getHandler()->getApiVars($this));

        return $data;
    }
}
