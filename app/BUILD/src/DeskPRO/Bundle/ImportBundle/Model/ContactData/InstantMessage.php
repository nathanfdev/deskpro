<?php

namespace DeskPRO\Bundle\ImportBundle\Model\ContactData;

use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class InstantMessage.
 */
class InstantMessage extends AbstractUserNameContactData
{
    /**
     * @var string
     *
     * @JMS\Type("string")
     *
     * @Assert\Choice(choices={"aim", "msn", "icq", "skype", "gtalk", "other"})
     */
    protected $service;

    /**
     * {@inheritdoc}
     */
    public function getContactType()
    {
        return 'instant_message';
    }

    /**
     * @return string
     */
    public function getService()
    {
        return $this->service;
    }

    /**
     * @param string $service
     */
    public function setService($service)
    {
        $this->service = $service;
    }
}
