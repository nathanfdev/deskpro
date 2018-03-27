<?php

namespace DeskPRO\Bundle\ImportBundle\Model\ContactData;

use JMS\Serializer\Annotation as JMS;

/**
 * Class AbstractUserNameContactData.
 */
abstract class AbstractUserNameContactData extends AbstractContactData
{
    /**
     * @var string
     *
     * @JMS\Type("string")
     */
    protected $username;

    /**
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * @param string $username
     */
    public function setUsername($username)
    {
        $this->username = $username;
    }
}
