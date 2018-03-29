<?php

namespace DeskPRO\Bundle\ImportBundle\Model\ContactData;

use JMS\Serializer\Annotation as JMS;

/**
 * Class Twitter.
 */
class Twitter extends AbstractUserNameContactData
{
    /**
     * @var bool
     *
     * @JMS\Type("boolean")
     */
    protected $displayFeed = false;

    /**
     * {@inheritdoc}
     */
    public function getContactType()
    {
        return 'twitter';
    }

    /**
     * @return bool
     */
    public function isDisplayFeed()
    {
        return $this->displayFeed;
    }

    /**
     * @param bool $displayFeed
     */
    public function setDisplayFeed($displayFeed)
    {
        $this->displayFeed = $displayFeed;
    }
}
