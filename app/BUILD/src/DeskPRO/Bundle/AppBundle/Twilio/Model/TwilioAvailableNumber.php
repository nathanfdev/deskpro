<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2018, DeskPRO Ltd.
 *
 * The license agreement under which this software is released
 * can be found at https://www.deskpro.com/eula/
 *
 * By using this software, you acknowledge having read the license
 * and agree to be bound thereby.
 *
 * Please note that DeskPRO is not free software. We release the full
 * source code for our software because we trust our users to pay us for
 * the huge investment in time and energy that has gone into both creating
 * this software and supporting our customers. By providing the source code
 * we preserve our customers' ability to modify, audit and learn from our
 * work. We have been developing DeskPRO since 2001, please help us make it
 * another decade.
 *
 * Like the work you see? Think you could make it better? We are always
 * looking for great developers to join us: http://www.deskpro.com/jobs/
 *
 * ~ Thanks, Everyone at Team DeskPRO
 */

namespace DeskPRO\Bundle\AppBundle\Twilio\Model;

use DeskPRO\Bundle\AppBundle\Entity\VoiceAccount;
use JMS\Serializer\Annotation as JMS;

/**
 * Class TwilioAvailableNumber.
 */
class TwilioAvailableNumber extends AbstractTwilioNumber
{
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
    protected $rateCenter;

    /**
     * @var string
     *
     * @JMS\Type("string")
     */
    protected $type;

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

    /**
     * Constructor.
     *
     * @param object       $apiNumber
     * @param VoiceAccount $account
     * @param bool         $added
     * @param string       $type
     * @param string       $price
     * @param string       $priceUnit
     * @param string       $priceUnit
     */
    public function __construct($apiNumber, VoiceAccount $account, $added, $type, $price, $priceUnit)
    {
        parent::__construct($apiNumber, $account, $added);

        $this->countryCode = $apiNumber->isoCountry;
        $this->region      = $apiNumber->region;
        $this->rateCenter  = $apiNumber->rateCenter;
        $this->type        = $type;
        $this->price       = $price;
        $this->priceUnit   = $priceUnit;
    }
}
