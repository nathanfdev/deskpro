<?php

namespace DeskPRO\Bundle\VoiceBundle\Plivo\Model;

use DeskPRO\Bundle\VoiceBundle\Model\AbstractVoiceNumber;
use JMS\Serializer\Annotation as JMS;

/**
 * Class AbstractPlivoNumber.
 */
abstract class AbstractPlivoNumber extends AbstractVoiceNumber
{
    /**
     * @var string
     *
     * @JMS\Type("entity<DeskPRO\Bundle\AppBundle\Entity\PlivoVoiceAccount>")
     */
    protected $account;

    /**
     * @var string
     *
     * @JMS\Type("string")
     */
    protected $region;

    /**
     * @var string
     *
     * @JMS\Type("string")
     */
    protected $voiceRate;

    /**
     * @var string
     *
     * @JMS\Type("string")
     */
    protected $smsRate;

    /**
     * @var string
     *
     * @JMS\Type("string")
     */
    protected $price;

    /**
     * @var string
     *
     * @JMS\Type("string")
     */
    protected $priceUnit;
}
