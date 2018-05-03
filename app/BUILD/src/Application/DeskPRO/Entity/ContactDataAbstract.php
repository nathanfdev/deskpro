<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\Entity;

use Application\DeskPRO\ContactData\ContactData;
use DeskPRO\Bundle\AppBundle\Validator\Constraints as AppAssert;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\GroupSequenceProviderInterface;

/**
 * Contact data is stuff like address, instant messaging, phone etc.
 * These can be applied to People and Organizations.
 *
 * Because of the nature, each 'data_type' uses each of the field1-field10
 * differently. Sometimes only a single one might be used, other times multiple.
 */
abstract class ContactDataAbstract extends \Application\DeskPRO\Domain\DomainObject implements GroupSequenceProviderInterface
{
    const TYPE_PHONE           = 'phone';
    const TYPE_WEBSITE         = 'website';
    const TYPE_INSTANT_MESSAGE = 'instant_message';
    const TYPE_TWITTER         = 'twitter';
    const TYPE_LINKED_IN       = 'linked_in';
    const TYPE_FACEBOOK        = 'facebook';
    const TYPE_ADDRESS         = 'address';

    const IM_AIM   = 'aim';
    const IM_MSN   = 'msn';
    const IM_ICQ   = 'icq';
    const IM_SKYPE = 'skype';
    const IM_GTALK = 'gtalk';
    const IM_OTHER = 'other';

    /**
     * The unique ID.
     *
     * @var int
     */
    protected $id = null;

    /**
     * The handler class.
     *
     * @var string
     *
     * @Assert\Choice(
     *     choices={"phone", "website", "instant_message", "twitter", "linked_in", "facebook", "address"},
     *     groups={"common"}
     * )
     */
    protected $contact_type;

    /**
     * The label/comment/name for this contact entry (Work, Home, etc).
     *
     * @var string
     *
     * @Assert\NotNull(groups={"common"})
     */
    protected $comment = '';

    /**
     * @var string
     *
     * @Assert\NotBlank(groups={"phone", "website", "instant_message", "twitter", "linked_in", "facebook", "address"})
     * @Assert\Url(groups={"website", "facebook", "linked_in"})
     * @AppAssert\ContactData\FacebookUrl(groups={"facebook"})
     * @AppAssert\ContactData\LinkedInUrl(groups={"linked_in"})
     */
    protected $field_1 = '';

    /**
     * @var string
     *
     * @Assert\NotBlank(groups={"address", "phone"})
     */
    protected $field_2 = '';

    /**
     * @var string
     *
     * @Assert\NotBlank(groups={"phone"})
     */
    protected $field_3 = '';

    /**
     * @var string
     */
    protected $field_4 = '';

    /**
     * @var string
     *
     * @Assert\NotBlank(groups={"address"})
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
     *
     * @AppAssert\PhoneNumber(groups={"phone"})
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
    protected $_save_callbacks = [];

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return object
     */
    abstract public function getRef();

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
        $pieces = [];
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

    /**
     * {@inheritdoc}
     */
    public function toApiData($primary = true, $deep = true, array $visited = [])
    {
        $data = parent::toApiData($primary, $deep, $visited);
        $data = array_merge($data, $this->getHandler()->getApiVars($this));

        return $data;
    }

    /**
     * @return array
     */
    public static function getInstantMessageTypes()
    {
        return [
            self::IM_AIM,
            self::IM_MSN,
            self::IM_ICQ,
            self::IM_SKYPE,
            self::IM_GTALK,
            self::IM_OTHER,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getGroupSequence()
    {
        $groups = ['common'];
        if ($this->contact_type) {
            $groups[] = $this->contact_type;
        }

        return $groups;
    }
}
