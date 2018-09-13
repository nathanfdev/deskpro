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
use DeskPRO\Bundle\VoiceBundle\Plivo\PlivoAdapter;
use DeskPRO\Bundle\VoiceBundle\Serializer\Model\PlivoEndpoint as PlivoEndpointModel;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class PlivoAccountsController.
 *
 * @ApiModes("all")
 * @Rest\Route("/voice_accounts/plivo")
 * @Feature("voice")
 * @ApiUserContext("admin", agent={"list", "get", "count"})
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
}
