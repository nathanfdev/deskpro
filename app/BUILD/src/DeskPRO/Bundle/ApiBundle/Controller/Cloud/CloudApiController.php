<?php

namespace DeskPRO\Bundle\ApiBundle\Controller\Cloud;

use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\Setting;
use Application\DeskPRO\Entity\TmpData;
use Application\EmailBundle\SwiftMailer\Message\Message;
use DeskPRO\Bundle\ApiBundle\Controller\BaseController;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiUserContext;
use DeskPRO\Bundle\AppBundle\DependencyInjection\SystemServices\EnvironmentService;
use DeskPRO\Bundle\AppBundle\Form\Error\Exception\InvalidFormException;
use DeskPRO\Bundle\AppBundle\Form\Type\People\PersonPasswordResetRequestType;
use DpSys\License;
use DpSys\LowError\SystemErrorHandler;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;
use Orb\Data\Countries;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Class ApiController.
 *
 * @ApiModes("all")
 * @ApiUserContext("open")
 * @Rest\Route("/cloud")
 */
class CloudApiController extends BaseController
{
    /**
     * {@inheritdoc}
     */
    public function __construct()
    {
        if (!defined('DPC_IS_CLOUD')) {
            exit;
        }
    }

    /**
     * @Rest\Get("/countries")
     * @ApiUserContext("open")
     * @ApiModes("all")
     *
     * @return Response
     */
    public function getCountriesAction()
    {
        $countries = Countries::getCountryArray();

        return new Response(json_encode($countries));
    }

    /**
     * @Rest\Get("/states")
     * @ApiUserContext("open")
     * @ApiModes("all")
     *
     * @return Response
     */
    public function getStatesAction()
    {
        $states = Countries::getUsStates();

        return new Response(json_encode($states));
    }

    /**
     * @Rest\Get("/eu_countries")
     * @ApiUserContext("open")
     * @ApiModes("all")
     *
     * @return Response
     */
    public function getEuCountriesAction()
    {
        $countries = Countries::getEuCountries();

        return new Response(json_encode($countries));
    }

    /**
     * @Rest\Get("/geo_ip")
     * @ApiUserContext("open")
     * @ApiModes("all")
     *
     * @return Response
     */
    public function getGeoIpAction()
    {
        /** @var EnvironmentService $env */
        $env = $this->container->get('app.environment');

        return $this->wrap([
            'geoip' => $env->getGeoIp(),
        ]);
    }

    /**
     * @Rest\Post("/submit_detail")
     * @ApiUserContext("admin")
     * @ApiModes("session")
     *
     * @param Request $request
     *
     * @return Response
     */
    public function postExtendDetailsAction(Request $request)
    {
        $this->assertValidDemoCall();

        $data = $request->request->all();

        $result = $this->callMa('set_site_cc_extend', [
            'form' => $data,
        ]);

        $this->getDoctrine()->getManager()->getRepository(Setting::class)->updateSetting('demo_was_extended', time());

        return $this->wrap($result);
    }

    /**
     * @Rest\Post("/preserve_data")
     * @ApiUserContext("admin")
     * @ApiModes("session")
     *
     * @return Response
     */
    public function postPreserveDataAction()
    {
        return $this->wrap([
            'agentUrl' => $this->get('router')->generate('agent', [], UrlGeneratorInterface::ABSOLUTE_URL),
        ]);
    }

    /**
     * @Rest\Post("/delete_feedback")
     * @ApiUserContext("admin")
     * @ApiModes("session")
     *
     * @param Request $request
     *
     * @return Response
     */
    public function postDeleteFeedbackAction(Request $request)
    {
        $this->assertValidDemoCall();

        $userMessage = $request->request->getAlnum('feedback', '');

        $this->callMa('cancel_site', [
            'user_message' => $userMessage,
        ]);

        return $this->wrap([
            'success' => true,
        ]);
    }

    /**
     * @Rest\Post("/question")
     * @ApiUserContext("admin")
     * @ApiModes("session")
     *
     * @param Request $request
     *
     * @return Response
     */
    public function postQuestionAction(Request $request)
    {
        $userMessage = $request->request->getAlnum('question', '');

        $this->callMa('submit_ticket', [
            'user_message' => $userMessage,
        ]);

        $this->assertValidDemoCall();

        return $this->wrap([
            'success' => true,
        ]);
    }

    /**
     * @Rest\Post("/reset_trial")
     * @ApiUserContext("admin")
     * @ApiModes("session")
     *
     * @return Response
     */
    public function postResetTrialAction()
    {
        $this->assertValidDemoCall();

        $queue = $this->container->getJobQueue();

        $types = [
            'users',
            'tickets',
            'organizations',
            'triggers',
            'filters',
            'templates',
            'escalations',
            'fields',
            'departments',
            'perms',
            'kb',
            'news',
            'downloads',
            'feedback',
            'labels',
            'snippets',
            'apps',
        ];

        //------------------------------
        // Status of any existing jobs
        //------------------------------

        $status = ['waiting' => false];
        $rep    = $this->getDoctrine()->getManager()->getRepository('DeskPRO:Job');

        foreach ($types as $type) {
            if (!$jobs = $rep->findBy(['type' => 'reset.'.$type], ['date_created' => 'desc'], 1)) {
                continue;
            }
            $s = $jobs[0]['status'];
            if (in_array($s, ['rejected', 'aborted'])) {
                $s = 'error';
            }
            if (in_array($status, ['inserting', 'reserved', 'processing'])) {
                $s = 'waiting';
            }
            $status[$type] = $s;

            if ('waiting' === $s) {
                $status['waiting'] = true;
            }
        }

        //------------------------------
        // Start new jobs as necessary
        //------------------------------

        $last = null;

        foreach ($types as $v) {
            if ('waiting' === @$status[$v]) {
                continue;
            }

            $job = $queue->add('reset.'.$v, [
                'context_person_id' => $this->getPerson()->getId(),
            ]);

            if ($last) {
                $job->depends_on_job = $last;
            }
            $last = $job;
        }

        $this->getDoctrine()->getManager()->flush();

        return new View($this->wrap([
            'success'  => true,
            'agentUrl' => $this->get('router')->generate('agent', [], UrlGeneratorInterface::ABSOLUTE_URL),
        ]));
    }

    /**
     * @Rest\Post("/forgot_password")
     * @ApiUserContext("open")
     * @ApiModes("all")
     *
     * @param Request $request
     *
     * @return Response
     */
    public function forgotPasswordAction(Request $request)
    {
        $this->assertValidDemoCall();

        $form = $this->createForm(PersonPasswordResetRequestType::class);
        $form->submit($request->request->all());

        if (!$form->isValid()) {
            throw new InvalidFormException($form);
        }

        $email     = $form->get('email')->getData();
        $person    = $this->get('data.person')->getPersonForEmail($email);
        $interface = 'agent';
        if (License::getLicense()->isPastExpireDate() || (defined('DPC_BILL_FAILED') && DPC_BILL_FAILED)) {
            $interface = 'billing';
        }

        $codeData = TmpData::create('reset-password', ['person_id' => $person['id'], 'interface' => $interface], '+3 days');
        $this->getManager()->persist($codeData);
        $this->getManager()->flush();

        if ($this->get('deskpro.feature_flags')->hasBeta('email_templates')) {
            $resetCode = $codeData->getCode();
            if ($person->isAgent()) {
                if ($interface == 'billing') {
                    $resetUrl = $this->get('router')->generate('billing_login', ['reset_code' => $resetCode], UrlGeneratorInterface::ABSOLUTE_URL);
                } else {
                    $resetUrl = $this->get('router')->generate('agent_login', ['reset_code' => $resetCode], UrlGeneratorInterface::ABSOLUTE_URL);
                }
            } else {
                $resetUrl = $this->get('router')->generate('portal_reset_password_process', ['code' => $resetCode], UrlGeneratorInterface::ABSOLUTE_URL);
            }
            $viewModel = $this->get('email.user_viewmodel_factory')
                ->createResetPasswordModel($resetUrl);
            $this->get('email.email_sender')
                ->send($viewModel, ['to' => $person]);
        } else {
            $vars = [
                'code'      => $codeData->getCode(),
                'person'    => $person,
                'email'     => $email,
                'interface' => $interface,
            ];

            /** @var Message $message */
            $message = $this->container->get('mailer')->createMessage();
            $message->setTemplate('DeskPRO:emails_user:reset-password.html.twig', $vars);
            $message->setTo($form->get('email')->getData(), $person->getDisplayName());

            $this->container->get('mailer')->send($message);
        }

        return new View();
    }

    private function assertValidDemoCall()
    {
        // these all only matter in demo mode
        // so prevent calling them any other time as a precaution
        if (!defined('DPC_DEMO_EXPIRE') || !DPC_DEMO_EXPIRE) {
            $extendedAt = $this->get('settings_resolver')->getGlobalSettings()->get('demo_was_extended');
            if (!$extendedAt || $extendedAt < (time() - 18000)) {
                throw $this->createNotFoundException('demo only');
            }
        }
    }

    /**
     * @param string $actionId
     * @param array  $data
     *
     * @return array
     */
    private function callMa($actionId, array $data = [])
    {
        $person = $this->getPerson();

        $tmpdata = new TmpData();
        $tmpdata->setType('dpc_'.$actionId);
        $tmpdata->setData('person_id', $person->getId());
        $tmpdata->setData('person_name', $person->getName());
        $tmpdata->setData('person_email', $person->getPrimaryEmailAddress());
        $tmpdata->setData('data', $data);
        $tmpdata->date_expire = new \DateTime('+10 minutes');

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
     * @return Person
     */
    private function getPerson()
    {
        $token = $this->get('security.token_storage')->getToken();

        return $token->getUser();
    }
}
