<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\AppBundle\Serializer\Model\ContactData;

use Application\DeskPRO\Entity\ContactDataAbstract;
use JMS\Serializer\Annotation as JMS;

/**
 * Class AbstractContactData.
 *
 * @JMS\ExclusionPolicy("none")
 */
abstract class AbstractContactData
{
    /**
     * The unique ID.
     *
     * @JMS\Type("integer")
     *
     * @var int
     */
    protected $id;

    /**
     * Contact type.
     *
     * @JMS\Type("string")
     *
     * @var string
     */
    protected $contact_type;

    /**
     * Comment attached to contact.
     *
     * @JMS\Type("string")
     *
     * @var string
     */
    protected $comment;

    /**
     * Constructor.
     *
     * @param ContactDataAbstract $contact_data
     */
    public function __construct(ContactDataAbstract $contact_data)
    {
        $this->id           = $contact_data->getId();
        $this->contact_type = $contact_data->getContactType();
        $this->comment      = $contact_data->getComment();
    }
}
