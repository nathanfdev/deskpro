<?php

namespace DeskPRO\Bundle\ApiBundle\Controller\Voice;

use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDoc;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiUserContext;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\Feature;
use DeskPRO\Bundle\AppBundle\Entity\VoiceAccount;
use DeskPRO\Bundle\AppBundle\Form\Error\Exception\InvalidFormException;
use DeskPRO\Bundle\AppBundle\Form\Type\Voice\VoiceAccountType;
use DeskPRO\Bundle\AppBundle\Form\Type\Voice\VoiceBuyNumberType;
use DeskPRO\Bundle\AppBundle\Twilio\Model\TwilioExistingNumber;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;
use Orb\Data\Countries;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Twilio\Exceptions\TwilioException;

/**
 * Class VoiceAccountsController.
 *
 * @ApiModes("all")
 * @Rest\Route("/voice_accounts")
 * @Feature("voice")
 * @ApiUserContext("admin")
 * @ApiDoc(target="all", section="Voice Channel", output="DeskPRO\Bundle\AppBundle\Entity\VoiceAccount")
 * @ApiDoc(
 *     target="postAction,putAction,testCredentialsAction",
 *     input={
 *      "class"="DeskPRO\Bundle\AppBundle\Form\Type\Voice\VoiceAccountType",
 *      "options"={
 *          "data"="DeskPRO\Bundle\AppBundle\Entity\VoiceAccount"
 *      }
 *     }
 * )
 */
class VoiceAccountsController extends AbstractVoiceCrudController
{
    public static $entity       = VoiceAccount::class;
    public static $type         = VoiceAccountType::class;
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
     * @param Request $request
     *
     * @Rest\Post("/test_credentials")
     *
     * @return View
     */
    public function testCredentialsAction(Request $request)
    {
        $account    = new VoiceAccount();
        $accountSid = $request->request->get('account_sid');

        if ($accountSid) {
            // pre-load existing account entity to avoid duplicate validation errors
            $existingAccount = $this->getManager()->getRepository(VoiceAccount::class)->findOneBy([
                'accountSid' => $accountSid,
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
     *     output="array<DeskPRO\Bundle\AppBundle\Twilio\Model\TwilioAvailableNumber>"
     * )
     *
     * @Rest\Get("/{account}/available_numbers")
     *
     * @param VoiceAccount $account
     * @param Request      $request
     *
     * @return View
     */
    public function getAvailableNumbersAction(VoiceAccount $account, Request $request)
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
     *     output="DeskPRO\Bundle\AppBundle\Twilio\Model\TwilioPaginate"
     * )
     *
     * @Rest\Get("/{account}/existing_numbers")
     *
     * @param VoiceAccount $account
     * @param Request      $request
     *
     * @return View
     */
    public function getExistingNumbersAction(VoiceAccount $account, Request $request)
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
     *       "class"="DeskPRO\Bundle\AppBundle\Form\Type\Voice\VoiceBuyNumberType"
     *     },
     *     output="DeskPRO\Bundle\AppBundle\Entity\VoiceNumber"
     * )
     *
     * @Rest\Post("/{account}/buy_number")
     *
     * @param VoiceAccount $account
     * @param Request      $request
     *
     * @return View
     */
    public function buyNumberAction(VoiceAccount $account, Request $request)
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
