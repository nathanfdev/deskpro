<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\AppBundle\Serializer\Model\ContactData;

use Application\DeskPRO\Entity\ContactDataAbstract;
use JMS\Serializer\Annotation as JMS;

/**
 * Class AbstractUserNameContactData.
 *
 * @JMS\ExclusionPolicy("none")
 */
class AbstractUserNameContactData extends AbstractContactData
{
    /**
     * @var string
     */
    protected $username;

    /**
     * {@inheritdoc}
     */
    public function __construct(ContactDataAbstract $contact_data)
    {
        parent::__construct($contact_data);

        $this->username = $contact_data->getField1();
    }
}
