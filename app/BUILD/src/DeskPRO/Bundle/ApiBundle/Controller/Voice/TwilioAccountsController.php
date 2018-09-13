<?php

namespace DeskPRO\Bundle\ApiBundle\Controller\Voice;

use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDoc;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiUserContext;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\Feature;
use DeskPRO\Bundle\AppBundle\Entity\TwilioVoiceAccount;
use DeskPRO\Bundle\AppBundle\Form\Error\Exception\InvalidFormException;
use DeskPRO\Bundle\VoiceBundle\Form\Type\TwilioAccountType;
use DeskPRO\Bundle\VoiceBundle\Form\Type\VoiceAccountType;
use DeskPRO\Bundle\VoiceBundle\Form\Type\VoiceBuyNumberType;
use DeskPRO\Bundle\VoiceBundle\Twilio\Model\TwilioExistingNumber;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;
use Orb\Data\Countries;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Twilio\Exceptions\TwilioException;

/**
 * Class TwilioAccountsController.
 *
 * @ApiModes("all")
 * @Rest\Route("/voice_accounts/twilio")
 * @Feature("voice")
 * @ApiUserContext("admin", agent={"list", "get", "count"})
 * @ApiDoc(target="all", section="Voice Channel", output="DeskPRO\Bundle\AppBundle\Entity\TwilioVoiceAccount")
 * @ApiDoc(
 *     target="postAction,putAction,testCredentialsAction",
 *     input={
 *      "class"="DeskPRO\Bundle\VoiceBundle\Form\Type\TwilioAccountType",
 *      "options"={
 *          "data"="DeskPRO\Bundle\AppBundle\Entity\TwilioVoiceAccount"
 *      }
 *     }
 * )
 */
class TwilioAccountsController extends AbstractVoiceCrudController
{
    public static $entity       = TwilioVoiceAccount::class;
    public static $type         = TwilioAccountType::class;
    public static $listOrder    = 'asc';
    public static $listPaginate = false;

    /**
     * @ApiDoc(
     *     description="Check if account credentials are correct",
     *     statusCodes={
     *         200="Returned if everything is ok"
     *     },
     *     noOutput=true
     * )
     *
     * @Rest\Post("/test_credentials")
     *
     * @param Request $request
     *
     * @throws \Exception
     *
     * @return View
     */
    public function testCredentialsAction(Request $request)
    {
        $account    = new TwilioVoiceAccount();
        $accountSid = $request->request->get('account_id');

        if ($accountSid) {
            // pre-load existing account entity to avoid duplicate validation errors
            $existingAccount = $this->getManager()->getRepository(TwilioVoiceAccount::class)->findOneBy([
                'accountId' => $accountSid,
            ]);

            if ($existingAccount) {
                $account = $existingAccount;
            }
        }

        $form = $this->createForm(VoiceAccountType::class, $account);
        $form->submit($request->request->all());
        if (!$form->isValid()) {
            throw new InvalidFormException($form);
        }

        return new View(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * @ApiDoc(
     *     description="Returns a list of available numbers to buy",
     *     statusCodes={
     *         200="Returned if everything is ok"
     *     },
     *     output="array<DeskPRO\Bundle\VoiceBundle\Twilio\Model\TwilioAvailableNumber>"
     * )
     *
     * @Rest\Get("/{account}/available_numbers")
     *
     * @param TwilioVoiceAccount $account
     * @param Request            $request
     *
     * @return View
     */
    public function getAvailableNumbersAction(TwilioVoiceAccount $account, Request $request)
    {
        $adapter     = $this->get('twilio_adapter');
        $query       = $request->query;
        $countryCode = strtoupper($query->get('country_code'));
        $options     = [];

        $region = $query->get('region');
        if ($region) {
            if (strlen($region) > 2) {
                if ($countryCode === 'US' && $stateCode = Countries::getUsStateCode($region)) {
                    // check as country code
                    $options['InRegion'] = $stateCode;
                } else {
                    // check as rate center
                    $options['InRateCenter'] = $region;
                }
            } else {
                // check as country code
                $options['InRegion'] = $region;
            }
        }
        if ($query->get('phrase')) {
            $options['Contains'] = $query->get('phrase');
        }

        $types = $query->get('types');
        if (!$types || !is_array($types)) {
            $types = [];
        }

        $numbers = [];
        foreach ($types as $type) {
            $numbers = array_merge($numbers, $adapter->getAvailablePhoneNumbers($account, $countryCode, $type, $options));
        }

        return new View($this->wrap($numbers));
    }

    /**
     * @ApiDoc(
     *     description="Returns a list of existing (bought) numbers",
     *     statusCodes={
     *         200="Returned if everything is ok"
     *     },
     *     filters={
     *          {"name"="page", "pattern"="\d", "description"="Which page to display", "dataType"="integer"}
     *     },
     *     output="DeskPRO\Bundle\VoiceBundle\Twilio\Model\TwilioPaginate"
     * )
     *
     * @Rest\Get("/{account}/existing_numbers")
     *
     * @param TwilioVoiceAccount $account
     * @param Request            $request
     *
     * @return View
     */
    public function getExistingNumbersAction(TwilioVoiceAccount $account, Request $request)
    {
        $page   = $request->query->getInt('page', 1);
        $result = $this->get('twilio_adapter')->getExistingPhoneNumbers($account, $page);

        return new View($this->wrap($result->getRecords(), [
            'page_num' => $result->getPageNum(),
            'has_next' => $result->hasNext(),
        ]));
    }

    /**
     * @ApiDoc(
     *     description="Buy number",
     *     statusCodes={
     *         200="Returned if everything is ok"
     *     },
     *     input={
     *       "class"="DeskPRO\Bundle\VoiceBundle\Form\Type\VoiceBuyNumberType"
     *     },
     *     output="DeskPRO\Bundle\AppBundle\Entity\VoiceNumber"
     * )
     *
     * @Rest\Post("/{account}/buy_number")
     *
     * @param TwilioVoiceAccount $account
     * @param Request            $request
     *
     * @throws \Exception
     *
     * @return View
     */
    public function buyNumberAction(TwilioVoiceAccount $account, Request $request)
    {
        $form = $this->createForm(VoiceBuyNumberType::class);
        $form->submit($request->request->all());
        if (!$form->isValid()) {
            throw new InvalidFormException($form);
        }

        try {
            $apiNumber = $this->get('twilio_adapter')->buyNumber($account, $form->getData());
            $model     = new TwilioExistingNumber($apiNumber, $account, false);

            return new View($this->wrap($model));
        } catch (TwilioException $e) {
            return $this->getFormErrorResponseFromException('twilio_exception', $e);
        }
    }
}
