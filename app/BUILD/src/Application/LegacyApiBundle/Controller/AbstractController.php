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

/**
 * DeskPRO.
 */

namespace Application\LegacyApiBundle\Controller;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity\CommentAbstract;
use Application\DeskPRO\Exception\ValidationException;
use Application\DeskPRO\Validator\ViolationApiRenderer;
use Application\LegacyApiBundle\HttpFoundation\JsonResponse;
use Application\LegacyApiBundle\Request\RequestAuth;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use DpSys\LowError\SystemErrorHandler;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Validator\ConstraintViolationList;

/**
 * Base API controller.
 *
 * @ApiModes("all")
 */
abstract class AbstractController extends \Application\DeskPRO\Controller\AbstractController
{
    /**
     * @var \Application\LegacyApiBundle\ApiUser
     */
    public $api_user;

    /**
     * The API key making this request.
     *
     * @var \Application\DeskPRO\Entity\ApiKey|null
     */
    public $apikey;

    /**
     * @var \Application\DeskPRO\Entity\ApiToken|null
     */
    public $api_token;

    /**
     * The user context (user making the request, or the one the API key says to use).
     *
     * @var \Application\DeskPRO\Entity\Person
     */
    public $person;

    /**
     * Entity manager.
     *
     * @var \Doctrine\ORM\EntityManager
     */
    public $em;

    /**
     * Plain database connection for raw queries.
     *
     * @var \Application\DeskPRO\DBAL\Connection
     */
    public $db;

    /**
     * Input reader.
     *
     * @var \Application\DeskPRO\Input\Reader
     */
    public $in;

    /**
     * A generic value cleaner.
     *
     * @var \Orb\Input\Cleaner\Cleaner
     */
    public $cleaner;

    /**
     * Shared template vars.
     *
     * @var \ArrayObject
     */
    public $tplvars;

    /**
     * @var \Application\DeskPRO\Templating\Engine
     */
    public $tpl;

    /**
     * Fetch settings.
     *
     * @var \Application\DeskPRO\Settings\Settings
     */
    public $settings;

    /**
     * @var null|array
     */
    public $rate_info = null;

    protected function init()
    {
        $this->em       = $this->get('doctrine.orm.entity_manager');
        $this->db       = $this->get('database_connection');
        $this->in       = $this->get('deskpro.core.input_reader');
        $this->cleaner  = $this->get('deskpro.core.input_cleaner');
        $this->settings = $this->get('deskpro.core.settings');

        /** @var \Application\LegacyApiBundle\Request\RequestAuth $request_auth */
        $request_auth   = $this->get('deskpro.api.request_auth');
        $this->api_user = $request_auth->getApiUser();

        $this->apikey    = $this->api_user->api_key;
        $this->api_token = $this->api_user->api_token;
        $this->person    = $this->api_user->person;

        if ($this->person && $this->person->is_agent && !$this->person->is_deleted && !$this->person->is_disabled) {
            App::setCurrentPerson($this->person);

            $this->person->loadHelper('Agent');
            $this->person->loadHelper('AgentTeam');
            $this->person->loadHelper('AgentPermissions');
            $this->person->loadHelper('PermissionsManager');
            $this->person->loadHelper('HelpMessages');
            $this->person->loadHelper('AgentPrefs');
        }
    }

    /**
     * Always require a valid API key.
     */
    public function preActionHandler(Request $request, $action, $arguments = null)
    {
        if (!$this->apikey && !$this->api_token) {
            $response = $this->createApiErrorResponse('invalid_auth', 'Please provide a valid API key or token', 401);
            $response->headers->add([
                'WWW-Authenticate' => 'Basic realm="API"',
            ]);

            return $response;
        }

        if ($this->apikey) {
            $this->container->getTicketManager()->setAutoContextVar('api_key', $this->apikey);
        }

        if ($this->api_token && $this->api_token->date_expires && $this->api_token->date_expires->getTimestamp() < time()) {
            return $this->createApiErrorResponse('token_expired', 'Your API token has expired. Please login again.', 403);
        }

        if (!$this->person) {
            return $this->createApiErrorResponse('invalid_person', 'Please provide a valid agent for this request', 403);
        }

        // Verify that token requests are with sessions, and verify the session person matches
        if ($this->api_token && $this->api_token->scope == 'session') {
            $session = $this->api_user->session;

            if (!$session || !$session->person || $session->person != $this->api_token->person) {
                return $this->createApiErrorResponse('invalid_api_token', 'API requests via token must be with a valid session', 403);
            }

            // Validate the request token
            if (!$this->api_user->request_token || !$this->api_user->session->checkSecurityToken('request_token', $this->api_user->request_token)) {
                return $this->createApiErrorResponse('invalid_request_token', 'You must provide a valid request token', 403);
            }

            // Ping the 'last' date of the session
            $this->container->getDb()->update('sessions', [
                'date_last' => date('Y-m-d H:i:s'),
            ], ['id' => $this->api_user->session->id]);

            // Increase lifetime of the session token
            $this->container->getDb()->update('api_token', [
                'date_expires' => date('Y-m-d H:i:s', strtotime('+1 hour')),
            ], ['id' => $this->api_token->id]);
        }

        if ($this instanceof ProtectedControllerInterface) {
            $perm_strategy = $this->getPermissionStrategy();
            $context_info  = [
                'controller' => $this,
                'action'     => $action,
                'arguments'  => $arguments,
                'type'       => $action,
            ];
            if (!$perm_strategy->userHasPermission($this->api_user, $context_info)) {
                return $this->createApiErrorResponse('no_permission', 'You do not have permission to use this resource', 403);
            }
        }

        if (App::getSetting('core.api_rate_limit')) {
            $error = $this->_checkRateLimit($action, $arguments);
            if ($error) {
                return $error;
            }
            $this->_updateRateLimit($action, $arguments);
        }
    }

    protected function _checkRateLimit($action, $arguments = null)
    {
        if ($this->apikey) {
            $this->rate_info = $this->em->getRepository('DeskPRO:ApiKey')->getRateLimitInfo($this->apikey);
        } else {
            $this->rate_info = $this->em->getRepository('DeskPRO:ApiToken')->getRateLimitInfo($this->api_token);
        }

        if ($this->rate_info['hits'] >= App::getSetting('core.api_rate_limit')) {
            return $this->createApiErrorResponse('rate_limit_exceeded', 'Rate Limit Exceeded', 429);
        }

        return;
    }

    protected function _updateRateLimit($action, $arguments = null)
    {
        if ($this->apikey) {
            $this->em->getRepository('DeskPRO:ApiKey')->updateRateLimit($this->apikey);
        } else {
            $this->em->getRepository('DeskPRO:ApiToken')->updateRateLimit($this->api_token);
        }

        if ($this->rate_info) {
            ++$this->rate_info['hits'];
        }
    }

    /**
     * Create an API error response.
     *
     * @param string $error_code    The short error code
     * @param string $error_message The error message
     * @param int    $status        The HTTP status to return
     *
     * @return Response
     */
    public function createApiErrorResponse($error_code, $error_message, $status = 400)
    {
        return $this->createApiResponse([
            'error_code'    => $error_code,
            'error_message' => $error_message,
        ], $status);
    }

    /**
     * Create an API error response.
     *
     * @param string $error_code    The short error code
     * @param string $error_message The error message
     * @param array  $data          Data to return
     * @param int    $status        The HTTP status to return
     *
     * @return Response
     */
    public function createApiErrorInfoResponse($error_code, $error_message, array $error_info, $status = 400)
    {
        return $this->createApiResponse([
            'error_code'    => $error_code,
            'error_message' => $error_message,
            'error_info'    => $error_info,
        ], $status);
    }

    /**
     * @param array $errors
     * @param int   $status
     *
     * @return Response
     */
    public function createApiMultipleErrorResponse(array $errors, $status = 400)
    {
        return $this->createApiResponse([
            'error_code' => 'multiple',
            'errors'     => $errors,
        ], $status);
    }

    /**
     * Create an API response.
     *
     * @param array $data
     * @param int   $status
     *
     * @return Response
     */
    public function createApiResponse(array $data, $status = 200)
    {
        $_SERVER['HTTP_ACCEPT'] = 'application/json';

        $response = $this->createJsonResponse($data, $status);

        if ($this->rate_info) {
            $response->headers->set('X-RateLimit-Limit', App::getSetting('core.api_rate_limit'));
            $response->headers->set('X-RateLimit-Remaining', max(0, App::getSetting('core.api_rate_limit') - $this->rate_info['hits']));
            $response->headers->set('X-RateLimit-Reset', $this->rate_info['reset_stamp']);
        }

        /** @var RequestAuth $auth */
        $auth = $this->get('deskpro.api.request_auth');
        if ($auth && ($log = $auth->getApiLogEntry())) {
            $em = $this->get('doctrine.orm.entity_manager');

            // Dont log rate limit
            if ($status == 429) {
                $em->remove($log);
            } else {
                $settings              = $this->get('settings_resolver')->getGlobalSettings();
                $maxResponseBodyLength = $settings->get('api_log.max_response_body_length', 1024 * 1024);

                $encodedData = json_encode($data);
                if ($maxResponseBodyLength && mb_strlen($encodedData, '8bit') > $maxResponseBodyLength) {
                    $data = substr($encodedData, 0, $maxResponseBodyLength);
                }

                $log->setResponse([
                    'status'  => $status,
                    'content' => $data,
                ]);
            }
            $em->flush();
        }

        return $response;
    }

    /**
     * Creates an API success response.
     *
     * @param array $extra
     * @param int   $status
     *
     * @return Response|JsonResponse
     */
    public function createSuccessResponse(array $extra = [], $status = 200)
    {
        return $this->createApiResponse(['success' => true] + $extra, $status);
    }

    /**
     * Creates an API success response.
     *
     * @param array $extra
     * @param int   $status
     *
     * @return Response
     */
    public function createApiSuccessResponse(array $extra = [], $status = 200)
    {
        return $this->createApiResponse(['success' => true] + $extra, $status);
    }

    /**
     * Create API response for after anew resource was created.
     *
     * @param array $data
     * @param       $url
     *
     * @return Response
     */
    public function createApiCreateResponse(array $data, $url)
    {
        $response = $this->createApiResponse($data, 201);

        $isIIS                 = false !== strpos(strtolower($this->request->server->get('SERVER_SOFTWARE')), 'microsoft-iis');
        $locationStripDisabled = $this->settings->get('api.disable_location_header_strip');

        if (!($response->getContent() && $isIIS && !$locationStripDisabled)) {
            $response->headers->add(['Location' => $url]);
        }

        return $response;
    }

    /**
     * Creates an API response to return after a resource is deleted. Typically you should return the 'old id' in $extra.
     *
     * @param array $extra
     * @param int   $status
     *
     * @return Response
     */
    public function createApiDeleteResponse(array $extra = [], $status = 200)
    {
        return $this->createApiResponse(['success' => true] + $extra, $status);
    }

    /**
     * @param ConstraintViolationList|ConstraintViolationList[] $errors A violation list, or an array of violation lists keyed by some prefix
     * @param array                                             $extra  Any other extra data you want to return
     * @param int                                               $status The HTTP status code to return
     *
     * @throws \InvalidArgumentException
     *
     * @return Response
     */
    public function createApiValidationErrorResponse($errors, array $extra = null, $status = 400)
    {
        $renderer = new ViolationApiRenderer();

        if ($errors instanceof ConstraintViolationList) {
            $info = $renderer->renderViolationList($errors);
        } elseif (is_array($errors)) {
            foreach ($errors as $k => $v) {
                if (!is_string($k) || !($v instanceof ConstraintViolationList)) {
                    throw new \InvalidArgumentException('$errors should be a single ConstraintViolationList, or an array of key=>ConstraintViolationList');
                }
            }
            $info = $renderer->renderCombinedViolationList($errors);
        }

        $data = [
            'error_code'    => 'validation_error',
            'error_message' => 'One or more validation errors occurred. Your request was not processed.',
            'errors'        => $info,
        ];

        if ($extra) {
            $data = array_merge($data, $extra);
        }

        return $this->createApiResponse($data, $status);
    }

    /**
     * @param Form  $form   A form to fetch errors from
     * @param array $extra  Any other extra data you want to return
     * @param int   $status The HTTP status code to return
     *
     * @throws \InvalidArgumentException
     *
     * @return Response
     */
    public function createApiFormErrorResponse(Form $form, array $extra = null, $status = 400)
    {
        $renderer = new ViolationApiRenderer();
        $info     = $renderer->renderFormErrorList($form);

        $data = [
            'error_code'    => 'validation_error',
            'error_message' => 'One or more validation errors occurred. Your request was not processed.',
            'errors'        => $info,
        ];

        if ($extra) {
            $data = array_merge($data, $extra);
        }

        return $this->createApiResponse($data, $status);
    }

    public function getApiData($input, $deep = true)
    {
        if (is_array($input) || $input instanceof \Traversable) {
            $output = [];
            foreach ($input as $key => $value) {
                if ($value instanceof \Application\DeskPRO\Domain\DomainObject) {
                    $output[$key] = $value->toApiData(false, $deep);
                }
            }

            return $output;
        } elseif ($input instanceof \Application\DeskPRO\Domain\DomainObject) {
            return $input->toApiData(true, $deep);
        }

        return false;
    }

    /**
     * @param $entity
     *
     * @return bool
     */
    public function isEntityValid($entity)
    {
        return sizeof($this->get('validator')->validate($entity)) == 0;
    }

    /**
     * @param \Symfony\Component\Form\Form $form
     *
     * @return string
     */
    public function getFormValidationErrorsString($form)
    {
        $result = [];
        $errors = $this->get('validator')->validate($form);

        if (sizeof($errors) > 0) {
            foreach ($errors as $error) {
                $result[] = $error->getMessage();
            }
        }

        return implode(',', $result);
    }

    /**
     * If we have some form and data from request we get rid off unnecessary data
     * in order not to have 'This form should not contain extra fields'.
     *
     * @param \Symfony\Component\Form\Form $form
     * @param array                        $requestData
     * @param array|string                 $keys
     *
     * @return array
     */
    public function deleteExtraDataFromRequest(Form $form, array $requestData, $keys = null)
    {
        if (is_null($keys)) {
            $form_data   = $form->all();
            $requestData = array_intersect_key($requestData, $form_data);

            return $requestData;
        } else {
            if (is_string($keys)) {
                $form_data   = $form->get($keys)->all();
                $requestData = array_intersect_key($requestData[$keys], $form_data);

                return [$keys => $requestData];
            } else {
                $result = [];

                foreach ($keys as $key) {
                    $form_data = $form->get($key)->all();

                    // this is workaround for 'collection' type - corresponding form data will be empty array

                    if (is_array($form_data) && empty($form_data)) {
                        $result[$key] = $requestData[$key];
                    } else {
                        $resultData = array_intersect_key($requestData[$key], $form_data);

                        $result[$key] = $resultData;
                    }
                }

                return $result;
            }
        }
    }

    public function getApiSearchResult($type, array $terms, array $extra, $cache_id, \Application\DeskPRO\Searcher\SearcherAbstract $searcher)
    {
        if ($cache_id) {
            $result_cache = $this->em->createQuery('
                SELECT r
                FROM DeskPRO:ResultCache r
                WHERE r.id = ?0
            ')->setParameters([$cache_id])->getOneOrNullResult();
            if ($result_cache) {
                if ($result_cache->person->id != $this->person->id || $result_cache->results_type !== $type) {
                    $result_cache = null;
                }
            }
        } else {
            $result_cache = null;
        }

        if (!$result_cache) {
            $searcher->setPerson($this->person);
            foreach ($terms as $term) {
                $searcher->addTerm($term['type'], $term['op'], $term['options']);
            }
            if (isset($extra['order_by'])) {
                $searcher->setOrderByCode($extra['order_by']);
            }

            $results = $searcher->getMatches();

            $result_cache               = new \Application\DeskPRO\Entity\ResultCache();
            $result_cache->person       = $this->person;
            $result_cache->results      = $results;
            $result_cache->results_type = $type;
            $result_cache->criteria     = $terms;
            $result_cache->num_results  = count($results);
            foreach ($extra as $key => $value) {
                $result_cache->setExtraData($key, $value);
            }

            $this->em->persist($result_cache);
            $this->em->flush();
        }

        return $result_cache;
    }

    public function getCustomFieldInput($input_name = 'field')
    {
        $custom_fields = $this->in->getArrayValue($input_name) ?: [];

        $output = [];
        foreach ($custom_fields as $key => $value) {
            if (is_numeric($key)) {
                $output['field_'.$key] = $value;
            } elseif (preg_match('/^field_\d+/', $key)) {
                $output[$key] = $value;
            }
        }

        return $output;
    }

    protected function _sendCommentApprovedNotification(CommentAbstract $comment)
    {
        if ($comment->getUserEmail()) {
            if ($this->get('deskpro.feature_flags')->hasFeature('new_email_templates')) {
                if ($comment->getPerson()) {
                    $to = $comment->getPerson();
                } else {
                    $to = $comment->getUserEmail();
                }
                $viewModel = $this->get('email.user_viewmodel_factory')
                    ->createCommentApprovedModel($comment);
                $this->get('email.email_sender')
                    ->send($viewModel, ['to' => $to]);
            } else {
                $message = $this->container->getMailer()->createMessage();
                if ($comment->getPerson()) {
                    $message->setTo($comment->getPerson()->getPrimaryEmailAddress(), $comment->getPerson()->getDisplayName());
                } else {
                    $message->setTo($comment->getUserEmail());
                }
                $message->setTemplate(
                    'DeskPRO:emails_user:comment-approved.html.twig',
                    [
                        'comment' => $comment,
                    ]
                );
                $this->container->getMailer()->send($message);
            }
        }

        // For feedback we also notify everyone involved
        if ($comment instanceof \Application\DeskPRO\Entity\FeedbackComment) {
            $commenting = new \Application\DeskPRO\Feedback\FeedbackCommenting($this->container, $this->person);
            $commenting->newCommentNotify($comment);
        }
    }

    protected function _sendCommentDeletedNotification(CommentAbstract $comment)
    {
        if ($comment->getUserEmail()) {
            if ($this->get('deskpro.feature_flags')->hasFeature('new_email_templates')) {
                if ($comment->getPerson()) {
                    $to = $comment->getPerson();
                } else {
                    $to = $comment->getUserEmail();
                }
                $viewModel = $this->get('email.user_viewmodel_factory')
                    ->createCommentDeletedModel($comment);
                $this->get('email.email_sender')
                    ->send($viewModel, ['to' => $to]);
            } else {
                $message = $this->container->getMailer()->createMessage();
                if ($comment->getPerson()) {
                    $message->setTo($comment->getPerson()->getPrimaryEmailAddress(), $comment->getPerson()->getDisplayName());
                } else {
                    $message->setTo($comment->getUserEmail());
                }
                $message->setTemplate(
                    'DeskPRO:emails_user:comment-deleted.html.twig',
                    [
                        'comment' => $comment,
                    ]
                );
                $this->container->getMailer()->send($message);
            }
        }
    }

    /**
     * @param \Exception $e
     *
     * @return Response
     */
    public function handleActionException(\Exception $e)
    {
        if ($e instanceof ValidationException) {
            return $this->createApiResponse(
                [
                    'error_code'    => 'validation_error',
                    'error_message' => 'There was a validation error while processing your request.',
                    'detail'        => [
                        'code'      => $e->getCode(),
                        'code_name' => $e->getCodeName(),
                        'message'   => $e->getMessage(),
                    ],
                ],
                400
            );
        } elseif ($e instanceof NotFoundHttpException) {
            return $this->createApiResponse([
                'error_code'    => 404,
                'error_message' => $e->getMessage() ?: 'Not Found',
            ], 404);
        } elseif ($e instanceof AccessDeniedHttpException) {
            return $this->createApiResponse([
                'error_code'    => 403,
                'error_message' => $e->getMessage() ?: 'Access Denied',
            ], 403);
        } elseif ($e instanceof AccessDeniedException) {
            return $this->createApiResponse([
                'error_code'    => 403,
                'error_message' => $e->getMessage() ?: 'Access Denied',
            ], 403);
        } else {
            SystemErrorHandler::logException($e);

            return $this->createApiResponse([
                'error_code'     => 500,
                'error_message'  => $e->getMessage(),
                'exception_code' => $e->getCode(),
                'exception_type' => get_class($e),
            ], 500);
        }

        return;
    }
}
