<?php

namespace DeskPRO\Bundle\ApiBundle\Controller\CloudEmails;

use JMS\Serializer\Annotation as JMS;

class DTOEmailMessage
{
    /**
     * @JMS\Type("string")
     *
     * @var string
     */
    private $messageId;

    /**
     * @JMS\Type("DateTime<'Y-m-d\TH:i:sP'>")
     *
     * @var \DateTime
     */
    private $receivedAt;

    /**
     * @JMS\Type("DeskPRO\Bundle\ApiBundle\Controller\CloudEmails\DTOMessageLocationS3")
     *
     * @var string
     */
    private $location;

    /**
     * @return string
     */
    public function getMessageId()
    {
        return $this->messageId;
    }

    /**
     * @param string $messageId
     */
    public function setMessageId( $messageId )
    {
        $this->messageId = $messageId;
    }

    /**
     * @return \DateTime
     */
    public function getReceivedAt()
    {
        return $this->receivedAt;
    }

    /**
     * @param \DateTime $receivedAt
     */
    public function setReceivedAt( $receivedAt )
    {
        $this->receivedAt = $receivedAt;
    }

    /**
     * @return string
     */
    public function getLocation()
    {
        return $this->location;
    }

    /**
     * @param string $location
     */
    public function setLocation( $location )
    {
        $this->location = $location;
    }


}
