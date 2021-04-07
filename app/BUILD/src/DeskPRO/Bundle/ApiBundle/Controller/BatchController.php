<?php

namespace DeskPRO\Bundle\ApiBundle\Controller;

use Application\DeskPRO\Entity\Person;
use DeskPRO\Bundle\ApiBundle\EventListener\JsonHeadersResponseListener;
use DeskPRO\Bundle\ApiBundle\Form\Type\Batch\GetBatchRequestType;
use DeskPRO\Bundle\ApiBundle\Form\Type\Batch\PostBatchRequestType;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use DeskPRO\Bundle\AppBundle\Form\Error\Exception\InvalidFormException;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\HttpKernelInterface;

/**
 * Class BatchController.
 *
 * @ApiModes("all")
 * @Rest\Route("/batch")
 */
class BatchController extends BaseController
{
    /**
     * @Rest\Post("")
     *
     * @param Request $request
     *
     * @return View
     */
    public function executeBatchPostAction(Request $request)
    {
        $form = $this->createForm(PostBatchRequestType::class);
        $form->submit($request->request->all(), true);
        if (!$form->isValid()) {
            throw new InvalidFormException($form);
        }

        $responses = [];
        foreach ($form->get('requests')->getData() as $identifier => $subRequestInfo) {
            $responses[$identifier] = $this->performSubRequest($subRequestInfo, $request);
        }

        return View::create([
            'responses' => $responses,
        ]);
    }

    /**
     * @Rest\Get("")
     *
     * @param Request $request
     *
     * @return View
     */
    public function executeBatchGetAction(Request $request)
    {
        $form = $this->createForm(GetBatchRequestType::class);
        $form->submit($request->query->all(), true);
        if (!$form->isValid()) {
            throw new InvalidFormException($form);
        }

        $responses = [];
        foreach ($form->get('get')->getData() as $identifier => $subRequestInfo) {
            $responses[$identifier] = $this->performSubRequest($subRequestInfo, $request);
        }

        return View::create([
            'responses' => $responses,
        ]);
    }

    /**
     * @param array   $info
     * @param Request $request
     *
     * @throws \Exception
     *
     * @return string
     */
    protected function performSubRequest($info, Request $request)
    {
        $json_serialized = null;
        if ($info['data']) {
            $json            = $info['data'];
            $json_serialized = @json_encode($json);
        }

        $subRequest = Request::create(
            $request->getUriForPath($info['url']),
            $info['method'],
            $info['params'],
            [],
            [],
            $request->server->all(),
            $json_serialized
        );

        if (!$this->get('request_matcher')->requestRouteExist($subRequest)) {
            throw $this->createBadRequestException("Route path for '{$info['url']}' not found");
        }

        $subRequest->headers->set('Content-Type', 'json');
        $subRequest->query->set(JsonHeadersResponseListener::INCLUDE_HEADERS_PARAM, 1);

        if ($info['headers']) {
            foreach ($info['headers'] as $k => $v) {
                $subRequest->headers->set($k, $v);
            }
        }

        // reset entity manager
        $this->getDoctrine()->getManager()->clear();

        // reset lazy choices in forms
        $container = $this->getContainer();

        $reflection = new \ReflectionClass($container);
        $property   = $reflection->getProperty('services');
        $property->setAccessible(true);

        $services = $property->getValue($container);
        foreach ($services as $name => $service) {
            if (strpos($name, 'form') !== false) {
                unset($services[$name]);
            }
        }

        $property->setValue($container, $services);
        unset($services);

        // refresh token
        if ($this->getUser() instanceof Person) {
            $token = $this->container->get('security.token_storage')->getToken();
            $token->setUser($this->getRepository(Person::class)->find($this->getUser()->getId()));
        }

        $response = $this->getKernel()->handle($subRequest, HttpKernelInterface::SUB_REQUEST);

        return $this->get('serializer')->deserialize($response->getContent(), 'array', 'json');
    }
}
