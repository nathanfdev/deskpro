<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\AppBundle\Serializer\Model\ContactData;

use Application\DeskPRO\Entity\ContactDataAbstract;
use JMS\Serializer\Annotation as JMS;

/**
 * Class Address.
 *
 * @JMS\ExclusionPolicy("none")
 */
class Address extends AbstractContactData
{
    /**
     * @var string
     */
    protected $address;

    /**
     * @var string
     */
    protected $city;

    /**
     * @var string
     */
    protected $state;

    /**
     * @var string
     */
    protected $zip;

    /**
     * @var string
     */
    protected $country;

    /**
     * {@inheritdoc}
     */
    public function __construct(ContactDataAbstract $contact_data)
    {
        parent::__construct($contact_data);

        $this->address = $contact_data->getField1();
        $this->city    = $contact_data->getField2();
        $this->state   = $contact_data->getField3();
        $this->zip     = $contact_data->getField4();
        $this->country = $contact_data->getField5();
    }
}
