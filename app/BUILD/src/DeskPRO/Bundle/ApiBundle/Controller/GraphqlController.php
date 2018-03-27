<?php

namespace DeskPRO\Bundle\ApiBundle\Controller;

use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDoc;
use DeskPRO\Bundle\ApiBundle\ApiDoc\GraphQL\Exception\NotFoundException;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiUserContext;
use Exception;
use GraphQL\Error\Debug;
use GraphQL\GraphQL;
use GraphQL\Type\Definition\ListOfType;
use GraphQL\Type\Definition\NonNull;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Schema;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Handles GraphQL schema requests and queries.
 *
 * @ApiModes("all")
 */
class GraphqlController extends BaseController
{
    /**
     * These GraphQL operations are broken. For now they are
     * excluded from the schema.
     *
     * @var array
     */
    protected $ignoredOperations = [
        'tickets_get_ticket_layouts_js',
        'tickets_get_20170401_ticket_layouts',
        'tickets_get_20170401_ticket_layouts_js',
        'auth_get_api_tokens_session',
        'apps_get_apps_zapier_ping',
        'email_templates_get_email_templates_info',
        'email_templates_get_email_templates_legacy_templates',
        'email_templates_get_email_templates_revert_legacy_template',
        'feedback_get_feedback',
        'helpdesk_get_helpdesk_agent_client_settings',
        'languages_get_languages_admin_phrases',
        'auth_get_me_device_setup_token',
        'notifications_and_alerts_get_notify_setup_action_alerts_clients',
        'organizations_get_organization_custom_fields',
        'organizations_get_organizations',
        'people_get_person_custom_fields',
        'portal_new_settings_get_settings_brands_new_portal_general',
        'snippets_get_snippets_csv',
        'ticket_form_widget_get_ticket_form_widget_code',
        'tickets_get_ticket_custom_fields',
        'tickets_get_tickets',
        'chats_get_user_chat_custom_fields',
        'widget_sample_online_agents_get_widget_live_demo_sample_state',
    ];

    /**
     * Handles GraphQL schema requests and queries.
     *
     * @Route("/graphql", name="api_v2_graphql")
     * @ApiUserContext("open")
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function graphqlAction(Request $request)
    {
        try {
            $schema = $this->getSchema();
            $input  = json_decode($request->getContent(), true);

            $result = GraphQL::executeQuery(
                $schema,
                $input['query'],
                null,
                $request,
                isset($input['variables']) ? $input['variables'] : null
            );

            $output = $result->toArray(Debug::RETHROW_INTERNAL_EXCEPTIONS);
        } catch (NotFoundException $e) {
            $output = [
                'errors' => [
                    [
                        'message' => 'Not Found',
                        'field'   => $e->getResolveInfo()->fieldName,
                    ],
                ],
            ];
        } catch (Exception $e) {
            $output = [
                'errors' => [
                    [
                        'message' => $e->getMessage(),
                    ],
                ],
            ];
        }

        return new JsonResponse($output);
    }

    /**
     * Handles GraphiQL requests.
     *
     * @Route("/graphiql", name="api_v2_graphiql")
     * @ApiUserContext("open")
     *
     * @return Response
     */
    public function graphiqlAction()
    {
        return $this->renderHtml('ApiBundle:graphql:graphiql.html.twig', [
            'url' => $this->generateUrl('api_v2_graphql'),
        ]);
    }

    /**
     * Handles request for GraphQL operations docs.
     *
     * @Route("/graphql/doc")
     * @ApiUserContext("open")
     *
     * @return Response
     */
    public function docAction()
    {
        $schema   = $this->getSchema();
        $query    = $schema->getQueryType();
        $mutation = $schema->getMutationType();
        $types    = $schema->getTypeMap();
        unset($types['Query'], $types['Mutation']);

        return $this->renderHtml('ApiBundle:graphql:doc.html.twig', [
            'query'    => $query,
            'mutation' => $mutation,
            'types'    => $types,
        ]);
    }

    /**
     * The resolver function which bridges the REST endpoints and GraphQL.
     *
     * The GraphQL schema generator converted REST endpoints into GraphQL operations.
     * This resolver converts an operation back into REST path, and makes
     * a sub-request to the appropriate endpoint. The return value of which
     * goes back to GraphQL.
     *
     * @param Request     $request    Request passed to indexAction
     * @param ResolveInfo $info       Information for field resolution
     * @param ApiDoc      $annotation Annotation related to the request
     * @param array       $args       Operation arguments
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function resolve(Request $request, ResolveInfo $info, ApiDoc $annotation, array $args)
    {
        $body    = null;
        $cookies = $request->cookies->all();
        $server  = $request->server->all();
        $method  = $annotation->getMethod();
        $path    = $annotation->getRoute()->getPath();

        // The GraphQL schema generator converts REST paths like "/content/article/{id}"
        // into GraphQL operations like "content_article (id: ID)". The operation
        // arguments must be converted back into REST paths so that a sub-request
        // can be made.

        // Some of the operation arguments do not come from the REST path. For
        // example form values. They will be saved, JSON encoded, and passed along
        // as the body of the sub-request.
        foreach ($args as $name => $value) {
            $regex = '/{'.preg_quote($name).'}/i';
            if (is_scalar($value) && preg_match($regex, $path, $matches)) {
                $path = str_replace($matches[0], $value, $path);
                unset($args[$name]);
            }
        }
        if (!empty($args)) {
            if (count($args) === 1) {
                $args = array_shift($args);
            }
            $body = is_scalar($args) ? $args : json_encode($args);
        }

        // Making a sub-request to the original REST endpoint!
        $subRequest = Request::create($path, $method, [], $cookies, [], $server, $body);
        if ($request->getSession()) {
            $subRequest->setSession($request->getSession());
        }
        try {
            $response = $this->getKernel()->handle(
                $subRequest,
                // TODO master request because Security ony runs on master, but this /graphql needs to be open
                // so no auth is enabled for this url (in api_config.yml)
                // ideally we need to fix @ApiUserContext("open") annote so open apis dont require us to completely disable security in api_config
                HttpKernelInterface::MASTER_REQUEST,
                false
            );
        } catch (NotFoundHttpException $e) {
            throw new NotFoundException($e->getMessage(), $info);
        }

        // Most of the REST endpoints return an ApiWrapper. We must extract
        // the value from "data" array it creates.
        $body = json_decode($response->getContent(), true);
        $body = (is_array($body) && isset($body['data'])) ? $body['data'] : $body;

        // The PUT/POST/DELETE endpoints may return null, which isn't a
        // valid GraphQL data type. Return a boolean instead.
        if ($body === null && in_array($method, ['PUT', 'POST', 'DELETE'])) {
            $body = true;
        }

        return $body;
    }

    /**
     * For debugging, spits out the GraphQL query operations JSON string.
     *
     * @Route("/graphql/doc/queries.json")
     *
     * @return Response
     */
    public function queriesJsonAction()
    {
        $schema = $this->getSchema();
        $query  = $schema->getQueryType();

        return new JsonResponse($this->buildQueryArray($query));
    }

    /**
     * For debugging, spits out the GraphQL mutation operations JSON string.
     *
     * @Route("/graphql/doc/mutations.json")
     *
     * @return Response
     */
    public function mutationsJsonAction()
    {
        $schema   = $this->getSchema();
        $mutation = $schema->getMutationType();

        return new JsonResponse($this->buildQueryArray($mutation));
    }

    /**
     * Used by queriesJsonAction() and mutationsJsonAction() to convert GraphQL
     * objects into an array.
     *
     * @param ObjectType $query
     *
     * @return array
     */
    protected function buildQueryArray(ObjectType $query)
    {
        $arr = [];
        foreach ($query->getFields() as $fields) {
            $args = [];
            foreach ($fields->args as $arg) {
                $args[$arg->name] = (string) $arg->getType();
            }

            $type = $fields->getType();
            if ($type instanceof NonNull || $type instanceof ListOfType) {
                $type = $type->getWrappedType();
            }

            $props = [];
            if (method_exists($type, 'getFields')) {
                foreach ($type->getFields() as $prop) {
                    $props[$prop->name] = (string) $prop->getType();
                }
            } elseif (!empty($type->name)) {
                $props[$type->name] = (string) $type;
            }

            $arr[$fields->name] = ['args' => $args, 'fields' => $props];
        }

        return $arr;
    }

    /**
     * Creates and returns a GraphQL schema from the @ApiDoc annotations.
     *
     * @return Schema
     */
    protected function getSchema()
    {
        $creator = $this->get('dp_api_doc.graphql.schema_creator');
        $creator->getSchemaConfig()
            ->setIgnoredOperations($this->ignoredOperations);

        $resolverFunc = [$this, 'resolve'];
        $annotations  = array_map(function ($doc) {
            return $doc['annotation'];
        }, $this->get('nelmio_api_doc.extractor.api_doc_extractor')->all());

        return $creator->createSchema($annotations, $resolverFunc);
    }

    /**
     * Renders a standard text/html response.
     *
     * Required because this code is in the ApiBundle, which wants to
     * send all responses as application/json by default.
     *
     * @param string $template
     * @param array  $parameters
     *
     * @return Response
     */
    protected function renderHtml($template, array $parameters = [])
    {
        return $this->render(
            $template,
            $parameters,
            new Response('', 200, ['Content-Type' => 'text/html'])
        );
    }
}
