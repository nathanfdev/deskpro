<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\AppBundle\Serializer\Model\ContactData;

use Application\DeskPRO\Entity\ContactDataAbstract;
use JMS\Serializer\Annotation as JMS;

/**
 * Class Phone.
 *
 * @JMS\ExclusionPolicy("none")
 */
class Phone extends AbstractContactData
{
    /**
     * @var string
     */
    protected $code;

    /**
     * @var string
     */
    protected $number;

    /**
     * @var string
     */
    protected $type;

    /**
     * {@inheritdoc}
     */
    public function __construct(ContactDataAbstract $contact_data)
    {
        parent::__construct($contact_data);

        $this->code   = $contact_data->getField1();
        $this->number = $contact_data->getField2();
        $this->type   = $contact_data->getField3();
    }
}
