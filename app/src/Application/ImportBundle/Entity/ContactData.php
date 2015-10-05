<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2015, DeskPRO Ltd.
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

namespace Application\ImportBundle\Entity;

use Application\ImportBundle\ContactData\ContactDataFactory;
use Symfony\Component\Validator\Constraints;
use Symfony\Component\Validator\Mapping\ClassMetadata;

/**
 * Exporting contact data entity.
 *
 * Class ContactData
 */
class ContactData extends AbstractEntity
{
    const TYPE_ADDRESS         = 'address';
    const TYPE_FACEBOOK        = 'facebook';
    const TYPE_FAX             = 'fax';
    const TYPE_INSTANT_MESSAGE = 'instant_message';
    const TYPE_LINKED_IN       = 'linked_in';
    const TYPE_MOBILE          = 'mobile';
    const TYPE_PHONE           = 'phone';
    const TYPE_SKYPE           = 'skype';
    const TYPE_TWITTER         = 'twitter';
    const TYPE_WEBSITE         = 'website';

    /**
     * @var string
     */
    private $contact_type;

    /**
     * @var string
     */
    private $comment;

    /**
     * @var string
     */
    private $field_1;

    /**
     * @var string
     */
    private $field_2;

    /**
     * @var string
     */
    private $field_3;

    /**
     * @var string
     */
    private $field_4;

    /**
     * @var string
     */
    private $field_5;

    /**
     * @var string
     */
    private $field_6;

    /**
     * @var string
     */
    private $field_7;

    /**
     * @var string
     */
    private $field_8;

    /**
     * @var string
     */
    private $field_9;

    /**
     * @var string
     */
    private $field_10;

    /**
     * {@inheritdoc}
     */
    public function getType()
    {
        return self::TYPE_CONTACT_DATA;
    }

    /**
     * @return string
     */
    public function getContactType()
    {
        return $this->contact_type;
    }

    /**
     * @param string $contact_type
     *
     * @return $this
     */
    public function setContactType($contact_type)
    {
        $this->contact_type = $contact_type;

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
     * @param string $comment
     *
     * @return $this
     */
    public function setComment($comment)
    {
        $this->comment = $comment;

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
     * @param string $field_1
     *
     * @return $this
     */
    public function setField1($field_1)
    {
        $this->field_1 = $field_1;

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
     * @param string $field_2
     *
     * @return $this
     */
    public function setField2($field_2)
    {
        $this->field_2 = $field_2;

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
     * @param string $field_3
     *
     * @return $this
     */
    public function setField3($field_3)
    {
        $this->field_3 = $field_3;

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
     * @param string $field_4
     *
     * @return $this
     */
    public function setField4($field_4)
    {
        $this->field_4 = $field_4;

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
     * @param string $field_5
     *
     * @return $this
     */
    public function setField5($field_5)
    {
        $this->field_5 = $field_5;

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
     * @param string $field_6
     *
     * @return $this
     */
    public function setField6($field_6)
    {
        $this->field_6 = $field_6;

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
     * @param string $field_7
     *
     * @return $this
     */
    public function setField7($field_7)
    {
        $this->field_7 = $field_7;

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
     * @param string $field_8
     *
     * @return $this
     */
    public function setField8($field_8)
    {
        $this->field_8 = $field_8;

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
     * @param string $field_9
     *
     * @return $this
     */
    public function setField9($field_9)
    {
        $this->field_9 = $field_9;

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
     * @param string $field_10
     *
     * @return $this
     */
    public function setField10($field_10)
    {
        $this->field_10 = $field_10;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function toArray()
    {
        $handler = ContactDataFactory::getHandler($this->contact_type);
        $params  = $handler->toArray($this);

        $params['oid'] = $this->oid;

        return $params;
    }

    /**
     * {@inheritdoc}
     */
    public static function loadValidatorMetadata(ClassMetadata $metadata)
    {
        AbstractEntity::loadValidatorMetadata($metadata);

        $metadata
            ->addPropertyConstraint('contact_type', new Constraints\NotBlank())
            ->addPropertyConstraint('contact_type', new Constraints\Choice(array(
                'choices' => array(
                    self::TYPE_ADDRESS,
                    self::TYPE_FACEBOOK,
                    self::TYPE_FAX,
                    self::TYPE_INSTANT_MESSAGE,
                    self::TYPE_LINKED_IN,
                    self::TYPE_MOBILE,
                    self::TYPE_PHONE,
                    self::TYPE_SKYPE,
                    self::TYPE_TWITTER,
                    self::TYPE_WEBSITE,
                ),
            )))
            ->addPropertyConstraint('field_1', new Constraints\NotBlank())
        ;
    }
}
