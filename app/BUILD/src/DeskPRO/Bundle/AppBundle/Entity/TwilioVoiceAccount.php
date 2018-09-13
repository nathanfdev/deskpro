<?php

namespace DeskPRO\Bundle\AppBundle\Entity;

use DeskPRO\Bundle\VoiceBundle\Validator\Constraints as VoiceAssert;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class VoiceAccount.
 *
 * @ORM\Entity()
 * @ORM\EntityListeners({"DeskPRO\Bundle\VoiceBundle\EventListener\Doctrine\TwilioAccountListener"})
 *
 * @VoiceAssert\TwilioVoiceAccount()
 */
class TwilioVoiceAccount extends AbstractVoiceAccount
{
    /**
     * @ORM\Column(name="twiml_app_sid", type="string", length=100, nullable=true)
     *
     * @var string
     */
    protected $twimlAppSid;

    /**
     * @return string
     */
    public function getTwimlAppSid()
    {
        return $this->twimlAppSid;
    }

    /**
     * @param string $twimlAppSid
     *
     * @return $this
     */
    public function setTwimlAppSid($twimlAppSid)
    {
        $this->setModelField('twimlAppSid', $twimlAppSid);

        return $this;
    }
}
