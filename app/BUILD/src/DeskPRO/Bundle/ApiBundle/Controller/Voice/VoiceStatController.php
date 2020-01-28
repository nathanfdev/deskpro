<?php

namespace DeskPRO\Bundle\ApiBundle\Controller\Voice;

use DeskPRO\Bundle\ApiBundle\Controller\BaseController;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiUserContext;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\Feature;
use DeskPRO\Bundle\AppBundle\Entity\AbstractVoiceAccount;
use DeskPRO\Bundle\AppBundle\Entity\TwilioVoiceAccount;
use DeskPRO\Bundle\VoiceBundle\Model\BillingSummary\BillingSummary;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class VoiceStatController.
 *
 * @Rest\Route("/voice_stats")
 * @ApiModes("all")
 * @Feature("voice")
 * @ApiUserContext("admin")
 */
class VoiceStatController extends BaseController
{
    /**
     * @Rest\Get("/{account}/billing_summary")
     *
     * @param AbstractVoiceAccount $account
     * @param Request              $request
     *
     * @throws \Exception
     *
     * @return View
     */
    public function getBillingSummaryAction(AbstractVoiceAccount $account, Request $request)
    {
        try {
            if ($request->query->get('date')) {
                $dateStart = new \DateTime($request->query->get('date'));
            } else {
                $dateStart = new \DateTime('first day of this month');
            }
        } catch (\Exception $e) {
            throw $this->createBadRequestException('Unable to parse date');
        }

        $dateEnd = clone $dateStart;
        $dateEnd->modify('+1 month -1 second');

        $billingSummary = new BillingSummary($this->get('dp.voice.billing_summary')->getDeskproBillingSummary($account, $dateStart, $dateEnd));
        if ($account instanceof TwilioVoiceAccount) {
            $billingSummary->setProviderStatRecords(
                $this->get('twilio_adapter')->getUsage($account, $dateStart, $dateEnd, [
                    'calls',
                    'calls-inbound',
                    'calls-inbound-tollfree',
                    'calls-inbound-local',
                    'calls-inbound-mobile',
                    'calls-outbound',
                    'calls-sip',
                    'calls-sip-inbound',
                    'calls-sip-outbound',
                    'calls-client',
                    'calls-recordings',
                    'calls-globalconference',
                    'phonenumbers',
                    'phonenumbers-tollfree',
                    'phonenumbers-mobile',
                    'phonenumbers-local',
                    'agent-conference',
                    'totalprice',
                    'transcriptions',
                    'marketplace-voicebase-transcription',
                ])
            );
        }

        return new View($this->wrap($billingSummary));
    }
}
