<?php

namespace DeskPRO\Bundle\ImportBundle\Model\ContactData;

use DeskPRO\Bundle\AppBundle\Validator\Constraints as AppAssert;
use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class Phone.
 */
class Phone extends AbstractContactData
{
    /**
     * @var string
     *
     * @JMS\Type("string")
     *
     * @Assert\NotBlank()
     * @AppAssert\PhoneNumber()
     */
    protected $number;

    /**
     * @var string
     *
     * @JMS\Type("string")
     *
     * @Assert\NotBlank()
     * @Assert\Choice(choices={"fax", "mobile", "phone"})
     */
    protected $type;

    /**
     * {@inheritdoc}
     */
    public function getContactType()
    {
        return 'phone';
    }

    /**
     * @return string
     */
    public function getNumber()
    {
        return $this->number;
    }

    /**
     * @param string $number
     */
    public function setNumber($number)
    {
        $this->number = $number;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }
}
