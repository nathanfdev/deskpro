<?php

namespace DeskPRO\Bundle\SendmailBundle\View\Model;

use JMS\Serializer\Annotation as JMS;

trait EventCodeEmailBaseType
{
    /**
     * A email specific code usable to filter emails
     *
     * @JMS\Type("string")
     *
     * @var string
     */
    protected $eventCode;

    /**
     * @var string
     */
    private $emailSourceId;

    /**
     * @param string $emailSourceId
     */
    public function setEmailSourceId($emailSourceId)
    {
        $this->emailSourceId = $emailSourceId;
    }

    /**
     * @return string
     */
    public function getEmailSourceId()
    {
        return $this->emailSourceId;
    }

    /**
     * @return string
     */
    public function getEventCodeType()
    {
        if (parent::getEventCodeType()) {
            return parent::getEventCodeType();
        }

        return '';
    }

    public function setEventCode()
    {
        $eventCode = $this->getEventCodeType();
        if ($eventCode) {
            $this->eventCode = 'Ref:Deskpro_'.$this->getEmailSourceId().' '.$eventCode.'_'.$this->getEmailSourceId();
        }
    }
}
