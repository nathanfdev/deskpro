<?php

namespace DeskPRO\Bundle\ApiBundle\Controller\Voice;

use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\Setting;
use Application\DeskPRO\Entity\TmpData;
use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDoc;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiUserContext;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\Feature;
use DeskPRO\Bundle\AppBundle\Entity\PlivoEndpoint;
use DeskPRO\Bundle\AppBundle\Entity\PlivoVoiceAccount;
use DeskPRO\Bundle\AppBundle\Form\Error\Exception\InvalidFormException;
use DeskPRO\Bundle\AppBundle\Security\Voter\PermissionGroups\PermissionGroupVoter;
use DeskPRO\Bundle\VoiceBundle\Form\Type\PlivoAccountType;
use DeskPRO\Bundle\VoiceBundle\Form\Type\VoiceAccountType;
use DeskPRO\Bundle\VoiceBundle\Form\Type\VoiceBuyNumberType;
use DeskPRO\Bundle\VoiceBundle\Plivo\Model\PlivoExistingNumber;
use DeskPRO\Bundle\VoiceBundle\Plivo\PlivoAdapter;
use DeskPRO\Bundle\VoiceBundle\Serializer\Model\PlivoEndpoint as PlivoEndpointModel;
use DpSys\LowError\SystemErrorHandler;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;
use Orb\Data\Countries;
use Plivo\Exceptions\PlivoResponseException;
use Symfony\Component\HttpFoundation\File\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\PreconditionFailedHttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use DeskPRO\Bundle\AppBundle\Serializer\Annotation\SerializerView;

/**
 * Class PlivoAccountsController.
 *
 * @ApiModes("all")
 * @Rest\Route("/voice_accounts/cloud")
 * @Feature("voice")
 * @ApiUserContext("admin", agent={"list", "get", "count", "refreshEndpoint"})
 */
class CloudAccountsController extends PlivoAccountsController
{
    public function __construct()
    {
        if (!defined('DPC_IS_CLOUD')) {
            exit;
        }
    }

    /**
     * @Rest\Post("")
     *
     * @param Request $request
     *
     * @return View
     */
    public function postAction(Request $request)
    {
        $this->denyAccessUnlessGranted(PermissionGroupVoter::CREATE, $this->getPermissionGroupContext($request));

        try {
            $this->initPlivoProxy();
        } catch (PreconditionFailedHttpException $e) {
            return new View([
                "errorList" => [
                    "dpms_client.no_funds" => "Your account has no funds."
                ]
            ], 400);
        }

        $this->blankFormVars($request);
        return $this->handleForm($this->getOrCreateEntity(), $request);
    }

    /**
     * @Rest\Put("/{id}", requirements={"id"="\d+"})
     *
     * @param int     $id
     * @param Request $request
     * @SerializerView(serializeNull=true)
     *
     * @return View
     */
    public function putAction($id, Request $request)
    {
        $this->denyAccessUnlessGranted(PermissionGroupVoter::MODIFY, $this->getPermissionGroupEntityContext($id, $request));
        $this->initPlivoProxy();
        $this->blankFormVars($request);
        return $this->handleForm($this->getOrCreateEntity(), $request);
    }

    /**
     * Sets input for the form fields normal plivo handler expects.
     *
     * @param Request $request
     * @return Request
     */
    private function blankFormVars(Request $request)
    {
        foreach ([
            "account_id" => "_",
            "auth_token" => "_",
            "account_name" => "_",
        ] as $k => $v) {
            $request->request->set($k, $v);
        }

        return $request;
    }

    /**
     * We only ever have 1 account.
     *
     * @return PlivoVoiceAccount
     */
    private function getOrCreateEntity()
    {
        $exist = $this->getRepository(PlivoVoiceAccount::class)->findOneBy([
            "accountId" => "_",
            "authToken" => "_"
        ]);

        if ($exist) {
            return $exist;
        }

        $create = new PlivoVoiceAccount();
        $create->setAccountId("_");
        $create->setAuthToken("_");
        $create->setAccountName("Deskpro Cloud Voice Account");
        return $create;
    }

    /**
     * Calls MA
     *
     * @param string $actionId
     * @param array $data
     * @return array
     */
    private function callMa($actionId, array $data = [])
    {
        /** @var Person $person */
        $person = $this->get('security.token_storage')->getToken()->getUser();

        $tmpdata = new TmpData();
        $tmpdata->setType('dpc_'.$actionId);
        $tmpdata->setData('person_id', $person->getId());
        $tmpdata->setData('person_name', $person->getName());
        $tmpdata->setData('person_email', $person->getPrimaryEmailAddress());
        $tmpdata->setData('data', $data);
        $tmpdata->setDateExpire(new \DateTime('+10 minutes'));

        $this->getDoctrine()->getManager()->persist($tmpdata);
        $this->getDoctrine()->getManager()->flush();

        $url = DP_MA_SERVER_SECURE.'/cloud/call/'.DPC_SITE_ID.'/'.$tmpdata->getCode();

        try {
            $client = new \Zend\Http\Client(null, ['timeout' => 15, 'sslverifypeer' => false]);
            $client->setMethod(\Zend\Http\Request::METHOD_GET);
            $client->setUri($url);
            $res = $client->send();

            $data = json_decode($res->getBody(), true);

            return $data;
        } catch (\Exception $e) {
            SystemErrorHandler::logException($e);
            throw $this->createNotFoundException();
        }
    }

    /**
     * Inits member services account
     *
     * @return array of [access, auth, url]
     */
    private function initMemberServicesAccount()
    {
        $settings    = $this->get('settings_resolver');
        $accessToken = $settings->getGlobalSettings()->get('dpms.access_token');
        $authToken   = $settings->getGlobalSettings()->get('dpms.auth_token');
        $dpmsUrl     = $settings->getGlobalSettings()->get('dpms.url');

        if ($accessToken && $authToken) {
            return [$accessToken, $authToken, $dpmsUrl];
        }

        $res = $this->callMa("init_ms_client_for_voice");

        if (!empty($res['error'])) {
            throw new PreconditionFailedHttpException($res['code']);
        }

        if (empty($res['accessToken']) || empty($res['accessToken'])) {
            throw new AccessDeniedException("dpms");
        }

        foreach ([
            'dpms.access_token' => $res['accessToken'],
            'dpms.auth_token'   => $res['accessToken'],
            'dpms.url'          => $res['dpmsUrl'],
        ] as $k => $v) {
            $this->get('doctrine.orm.entity_manager')
                ->getRepository(Setting::class)
                ->updateSetting("dpms.access_token", $res['accessToken']);
        }

        return [
            $res['accessToken'],
            $res['authToken'],
            $res['dpmsUrl']
        ];
    }

    /**
     * Inits plivo proxy settings
     */
    private function initPlivoProxy()
    {
        list (
            $accessToken,
            $authToken,
            $dpmsUrl
        ) = $this->initMemberServicesAccount();

        foreach ([
            'voice.plivo_proxy_host'     => "$dpmsUrl/voice/plivo-api/",
            'voice.plivo_proxy_username' => $accessToken,
            'voice.plivo_proxy_password' => $authToken,
        ] as $k => $v) {
            $this->get('doctrine.orm.entity_manager')
                ->getRepository(Setting::class)
                ->updateSetting($k, $v);
        }
    }
}