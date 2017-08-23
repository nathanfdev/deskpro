<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2017, DeskPRO Ltd.
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

namespace DeskPRO\Bundle\AppBundle\EventListener\Doctrine\Voice;

use DeskPRO\Bundle\AppBundle\Entity\VoiceNumber;
use DeskPRO\Bundle\AppBundle\Twilio\TwilioAdapter;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\Response;
use Twilio\Exceptions\RestException;

/**
 * Class VoiceNumberListener.
 */
class VoiceNumberListener
{
    /**
     * @var TwilioAdapter
     */
    private $twilioAdapter;

    /**
     * Constructor.
     *
     * @param TwilioAdapter $twilioAdapter
     */
    public function __construct(TwilioAdapter $twilioAdapter)
    {
        $this->twilioAdapter = $twilioAdapter;
    }

    /**
     * @ORM\PrePersist()
     *
     * @param VoiceNumber $number
     */
    public function onCreate(VoiceNumber $number)
    {
        $account = $number->getAccount();
        if (!$account) {
            return;
        }

        $this->twilioAdapter->updateNumber($number, [
            'voiceApplicationSid' => $account->getTwimlAppSid(),
        ]);
    }

    /**
     * @ORM\PreRemove()
     *
     * @param VoiceNumber $number
     *
     * @throws \Exception
     */
    public function onRemove(VoiceNumber $number)
    {
        try {
            $this->twilioAdapter->updateNumber($number, [
                'voiceApplicationSid' => '',
            ]);
        } catch (RestException $e) {
            if ($e->getStatusCode() === Response::HTTP_NOT_FOUND) {
                return;
            }

            throw $e;
        }
    }
}
