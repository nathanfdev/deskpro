<?php

namespace DeskPRO\Bundle\ApiBundle\Controller\Voice;

use Application\DeskPRO\Entity\Person;
use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDoc;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiUserContext;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\Feature;
use DeskPRO\Bundle\AppBundle\Entity\PlivoEndpoint;
use DeskPRO\Bundle\AppBundle\Entity\PlivoVoiceAccount;
use DeskPRO\Bundle\AppBundle\Form\Error\Exception\InvalidFormException;
use DeskPRO\Bundle\VoiceBundle\Form\Type\PlivoAccountType;
use DeskPRO\Bundle\VoiceBundle\Form\Type\VoiceAccountType;
use DeskPRO\Bundle\VoiceBundle\Form\Type\VoiceBuyNumberType;
use DeskPRO\Bundle\VoiceBundle\Plivo\Model\PlivoExistingNumber;
use DeskPRO\Bundle\VoiceBundle\Plivo\PlivoAdapter;
use DeskPRO\Bundle\VoiceBundle\Serializer\Model\PlivoEndpoint as PlivoEndpointModel;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;
use Orb\Data\Countries;
use Plivo\Exceptions\PlivoResponseException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class PlivoAccountsController.
 *
 * @ApiModes("all")
 * @Rest\Route("/voice_accounts/plivo")
 * @Feature("voice")
 * @ApiUserContext("admin", agent={"list", "get", "count", "refreshEndpoint"})
 * @ApiDoc(target="all", section="Voice Channel", output="DeskPRO\Bundle\AppBundle\Entity\PlivoVoiceAccount")
 * @ApiDoc(
 *     target="postAction,putAction,testCredentialsAction",
 *     input={
 *      "class"="DeskPRO\Bundle\VoiceBundle\Form\Type\PlivoAccountType",
 *      "options"={
 *          "data"="DeskPRO\Bundle\AppBundle\Entity\PlivoVoiceAccount"
 *      }
 *     }
 * )
 */
class PlivoAccountsController extends AbstractVoiceCrudController
{
    public static $entity       = PlivoVoiceAccount::class;
    public static $type         = PlivoAccountType::class;
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
        $account    = new PlivoVoiceAccount();
        $accountSid = $request->request->get('account_id');

        if ($accountSid) {
            // pre-load existing account entity to avoid duplicate validation errors
            $existingAccount = $this->getManager()->getRepository(PlivoVoiceAccount::class)->findOneBy([
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
     *     description="Regenerate a plivo endpoint for agent",
     *     statusCodes={
     *         200="Returned if everything is ok"
     *     },
     *     noOutput=true
     * )
     *
     * @Rest\Post("/{account}/refresh_endpoint/{agent}")
     *
     * @param PlivoVoiceAccount $account
     * @param Person            $agent
     *
     * @return View
     */
    public function refreshEndpointAction(PlivoVoiceAccount $account, Person $agent)
    {
        $endpoint = $this->getManager()->getRepository(PlivoEndpoint::class)->findOneBy([
            'account' => $account,
            'person'  => $agent,
        ]);

        if (!$endpoint) {
            $endpoint = new PlivoEndpoint();
            $endpoint
                ->setAccount($account)
                ->setPerson($agent);
        }

        $password = PlivoAdapter::createEndpointPassword();
        $resource = $this->get('plivo_adapter')->createEndpoint($account, $agent, $password);
        if ($resource) {
            $endpoint
                ->setUsername($resource->username)
                ->setPassword($password)
                ->setEndpointId($resource->endpointId)
            ;

            $this->getManager()->persist($endpoint);
            $this->getManager()->flush();
        }

        return new View($this->wrap(new PlivoEndpointModel($endpoint->getUsername(), $endpoint->getPassword())));
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
     * @param PlivoVoiceAccount $account
     * @param Request           $request
     *
     * @return View
     */
    public function getAvailableNumbersAction(PlivoVoiceAccount $account, Request $request)
    {
        $adapter     = $this->get('plivo_adapter');
        $query       = $request->query;
        $countryCode = strtoupper($query->get('country_code'));
        $options     = [];

        $region = $query->get('region');
        if ($region) {
            if (strlen($region) > 2) {
                if ($countryCode === 'US' && $stateCode = Countries::getUsStateCode($region)) {
                    // check as country code
                    $options['region'] = $stateCode;
                } else {
                    // check as rate center
                    $options['rate_center'] = $region;
                }
            } else {
                // check as country code
                $options['region'] = $region;
            }
        }
        if ($query->get('phrase')) {
            $options['pattern'] = $query->get('phrase');
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
     * @param PlivoVoiceAccount $account
     * @param Request           $request
     *
     * @return View
     */
    public function getExistingNumbersAction(PlivoVoiceAccount $account, Request $request)
    {
        $page   = $request->query->getInt('page', 1);
        $result = $this->get('plivo_adapter')->getExistingPhoneNumbers($account, $page);

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
     * @param PlivoVoiceAccount $account
     * @param Request           $request
     *
     * @throws \Exception
     *
     * @return View
     */
    public function buyNumberAction(PlivoVoiceAccount $account, Request $request)
    {
        $form = $this->createForm(VoiceBuyNumberType::class);
        $form->submit($request->request->all());
        if (!$form->isValid()) {
            throw new InvalidFormException($form);
        }

        try {
            $apiNumber = $this->get('plivo_adapter')->buyNumber($account, $form->getData());
            $model     = new PlivoExistingNumber($account, $apiNumber, false);

            return new View($this->wrap($model));
        } catch (PlivoResponseException $e) {
            return $this->getFormErrorResponseFromExceptionMessage('plivo_exception', $e->getErrorMessage());
        }
    }
}
