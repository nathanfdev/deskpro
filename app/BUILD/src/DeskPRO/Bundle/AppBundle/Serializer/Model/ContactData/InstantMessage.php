<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\AppBundle\Serializer\Model\ContactData;

use Application\DeskPRO\Entity\ContactDataAbstract;
use JMS\Serializer\Annotation as JMS;

/**
 * Class InstantMessage.
 *
 * @JMS\ExclusionPolicy("none")
 */
class InstantMessage extends AbstractUserNameContactData
{
    /**
     * @var string
     */
    protected $service;

    /**
     * {@inheritdoc}
     */
    public function __construct(ContactDataAbstract $contact_data)
    {
        parent::__construct($contact_data);

        $this->service = $contact_data->getField2();
    }
}
