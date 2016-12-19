<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2016, DeskPRO Ltd.
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
use libphonenumber\PhoneNumberUtil;

/**
 * Class AbstractTwilioNumber.
 */
abstract class AbstractTwilioNumber
{
    /**
     * @var string
     *
     * @JMS\Type("entity<DeskPRO\Bundle\AppBundle\Entity\VoiceAccount>")
     */
    protected $account;

    /**
     * @var string
     *
     * @JMS\Type("string")
     */
    protected $numberCountryCode;

    /**
     * @var string
     *
     * @JMS\Type("string")
     */
    protected $nationalNumber;

    /**
     * @var string
     *
     * @JMS\Type("string")
     */
    protected $number;

    /**
     * @var string
     *
     * @JMS\Type("string")
     */
    protected $nickname;

    /**
     * @var string
     *
     * @JMS\Type("string")
     */
    protected $countryCode;

    /**
     * @var array
     *
     * @JMS\Type("array<string>")
     */
    protected $capabilities = [];

    /**
     * @var bool
     *
     * @JMS\Type("boolean")
     */
    protected $added;

    /**
     * Constructor.
     *
     * @param object       $apiNumber
     * @param VoiceAccount $account
     * @param bool         $added
     */
    public function __construct($apiNumber, VoiceAccount $account, $added)
    {
        $phoneUtil = PhoneNumberUtil::getInstance();
        $number    = $phoneUtil->parse($apiNumber->phoneNumber, null);

        $this->numberCountryCode = $number->getCountryCode();
        $this->nationalNumber    = $number->getNationalNumber();
        $this->number            = $apiNumber->phoneNumber;

        $this->account  = $account;
        $this->nickname = $apiNumber->friendlyName;
        $this->added    = $added;

        foreach ($apiNumber->capabilities as $capability => $enabled) {
            if ($enabled) {
                $this->capabilities[] = $capability;
            }
        }
    }
}
