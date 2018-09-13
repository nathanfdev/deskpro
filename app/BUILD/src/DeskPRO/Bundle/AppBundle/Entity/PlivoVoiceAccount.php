<?php

namespace DeskPRO\Bundle\AppBundle\Entity;

use DeskPRO\Bundle\VoiceBundle\Validator\Constraints as VoiceAssert;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class PlivoVoiceAccount.
 *
 * @ORM\Entity()
 * @ORM\EntityListeners({"DeskPRO\Bundle\VoiceBundle\EventListener\Doctrine\PlivoAccountListener"})
 *
 * @VoiceAssert\PlivoVoiceAccount()
 */
class PlivoVoiceAccount extends AbstractVoiceAccount
{
    /**
     * @ORM\Column(name="user_application_id", type="string", length=100, nullable=true)
     *
     * @var string
     */
    protected $userApplicationId;

    /**
     * @ORM\Column(name="agent_application_id", type="string", length=100, nullable=true)
     *
     * @var string
     */
    protected $agentApplicationId;

    /**
     * @return string
     */
    public function getUserApplicationId()
    {
        return $this->userApplicationId;
    }

    /**
     * @param string $userApplicationId
     *
     * @return $this
     */
    public function setUserApplicationId($userApplicationId)
    {
        $this->setModelField('userApplicationId', $userApplicationId);

        return $this;
    }

    /**
     * @return string
     */
    public function getAgentApplicationId()
    {
        return $this->agentApplicationId;
    }

    /**
     * @param string $agentApplicationId
     *
     * @return $this
     */
    public function setAgentApplicationId($agentApplicationId)
    {
        $this->setModelField('agentApplicationId', $agentApplicationId);

        return $this;
    }
}
