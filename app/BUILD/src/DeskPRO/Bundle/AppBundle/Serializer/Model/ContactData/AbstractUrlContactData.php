<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\AppBundle\Serializer\Model\ContactData;

use Application\DeskPRO\Entity\ContactDataAbstract;
use JMS\Serializer\Annotation as JMS;

/**
 * Class AbstractUrlContactData.
 *
 * @JMS\ExclusionPolicy("none")
 */
abstract class AbstractUrlContactData extends AbstractContactData
{
    /**
     * @var string
     */
    protected $url;

    /**
     * {@inheritdoc}
     */
    public function __construct(ContactDataAbstract $contact_data)
    {
        parent::__construct($contact_data);

        $this->url = $contact_data->getField1();
    }
}
