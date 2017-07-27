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

namespace DeskPRO\Bundle\ApiBundle\Controller\Apps;

use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDoc;
use DeskPRO\Bundle\ApiBundle\Controller\BaseController;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiUserContext;
use DeskPRO\Bundle\AppBundle\Entity\AppStore\AppInstance;
use DeskPRO\Bundle\AppBundle\Util\HttpClient;
use FOS\RestBundle\Controller\Annotations as Rest;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\RequestOptions;
use Psr\Http\Message\ResponseInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class HttpProxyController.
 *
 * @ApiModes("session")
 * @ApiUserContext("agent")
 * @Rest\Route("/http-api-proxy")
 */
class HttpProxyController extends BaseController
{
    /**
     * @ApiDoc(
     *     section="Apps",
     *     description="App http proxy",
     *     statusCodes={
     *         200="Returned if everything is ok"
     *     },
     *     noInput=true,
     *     noOutput=true
     * )
     *
     * @Rest\Get("/{instance}")
     * @Rest\Post("/{instance}")
     * @Rest\Patch("/{instance}")
     * @Rest\Put("/{instance}")
     * @Rest\Delete("/{instance}")
     * @Rest\Head("/{instance}")
     * @Rest\Options("/{instance}")
     *
     * @ParamConverter("instance", class="AppBundle:Entity\AppStore\AppInstance", converter="DeskPRO\Bundle\AppStoreBundle\ParamConverter\AppInstanceParamConverter")
     *
     * @param AppInstance $instance
     * @param Request     $request
     *
     * @return string
     */
    public function proxyAction(AppInstance $instance, Request $request)
    {
        // create proxy request
        $proxyRequest = $this->get('api_proxy_request_factory')->createFromRequest(
            $instance,
            $request,
            $this->getUser()
        );

        // verify proxy request
        try {
            $this->get('api_proxy_validator')->validateProxyUrl($proxyRequest);
        } catch (\Exception $e) {
            throw $this->createBadRequestException($e->getMessage());
        }

        // send proxy request
        $httpClient = new HttpClient(['timeout' => 10]);
        $buildId    = $this->get('deskpro.app_env')->getBuildId();

        try {
            $options = [
                RequestOptions::HEADERS => array_merge(
                    $proxyRequest->getProxyHeaders(),
                    [
                        'X-Forwarded-For' => $request->getClientIp(),
                        'User-Agent'      => "DeskPRO/$buildId (API Proxy)",
                    ]
                ),
            ];

            if ($request->getContent()) {
                $options[RequestOptions::BODY] = $request->getContent();
            } elseif ($request->request->all()) {
                $options[RequestOptions::FORM_PARAMS] = $request->request->all();
            }

            $response = $httpClient->request(
                $proxyRequest->getProxyMethod(),
                $proxyRequest->getProxyUrl(),
                $options
            );

            return $this->getProxyResponse($response);
        } catch (RequestException $e) {
            if ($e->getResponse()) {
                return $this->getProxyResponse($e->getResponse());
            }

            return new Response($e->getMessage(), Response::HTTP_MISDIRECTED_REQUEST);
        } catch (\Exception $e) {
            return new Response($e->getMessage(), Response::HTTP_MISDIRECTED_REQUEST);
        }
    }

    /**
     * @param ResponseInterface $proxyResponse
     *
     * @return Response
     */
    private function getProxyResponse(ResponseInterface $proxyResponse)
    {
        $realStatusCode = $proxyResponse->getStatusCode();
        $statusCode     = $realStatusCode;
        if ($statusCode >= 500) {
            $statusCode = Response::HTTP_MISDIRECTED_REQUEST;
        }

        return new Response(
            $proxyResponse->getBody()->getContents(),
            $statusCode,
            array_merge(
                $proxyResponse->getHeaders(),
                [
                    'X-Real-Http-Code' => $realStatusCode,
                ]
            )
        );
    }
}
