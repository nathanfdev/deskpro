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

namespace DeskPRO\Bundle\ApiBundle\Controller;

use Application\DeskPRO\DependencyInjection\DeskproContainer;
use DeskPRO\Bundle\AppBundle\Serializer\ApiWrapper;
use DeskPRO\Component\Util\TypeUtils;
use DpSys\LowError\SystemErrorHandler;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\View\View;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\HttpKernelInterface;

/**
 * Class BaseController.
 */
class BaseController extends FOSRestController
{
    /**
     * @const DATA_TYPE_STANDARD Standard and sort of unknown datatype.
     */
    const DATA_TYPE_STANDARD = 1;

    /**
     * @const DATA_TYPE_GROUPED_COUNT Provided data is an array resulting from a grouped count query.
     */
    const DATA_TYPE_GROUPED_COUNT = 2;

    /**
     * @const DATA_TYPE_COUNT_ONLY Standard datatype, but we only want the total results
     */
    const DATA_TYPE_COUNT_ONLY = 3;

    /**
     * @param mixed $data
     * @param array $meta
     *
     * @return ApiWrapper
     */
    protected function wrap($data, array $meta = [])
    {
        return new ApiWrapper($data, $meta);
    }

    /**
     * @param $status
     * @param $code
     * @param $message
     * @param array|FormInterface $errorsData
     *
     * @return array
     */
    protected function createErrorRepresentation($status, $code, $message, $errorsData = [])
    {
        return [
            'status'  => $status,
            'code'    => $code,
            'message' => $message,
            'errors'  => count($errorsData) ? $errorsData : null,
        ];
    }

    /**
     * @param string     $code
     * @param \Exception $e
     *
     * @return View
     */
    protected function getFormErrorResponseFromException($code, \Exception $e)
    {
        return new View([
            'errors' => [
                'errors' => [
                    [
                        'code'    => $code,
                        'message' => $e->getMessage(),
                    ],
                ],
            ],
        ], Response::HTTP_BAD_REQUEST);
    }

    protected function createEntityNotFoundExceptionMessage($entity, $id) {
        return  "#{$id} Not Found";
    }

    /**
     * @param string $class
     * @param int    $id
     * @param string $message
     *
     * @return object
     */
    protected function findOr404($class, $id, $message = null)
    {
        if (!$entity = $this->getManager()->getRepository($class)->find($id)) {
            throw $this->createNotFoundException($message ?: $this->createEntityNotFoundExceptionMessage($class, $id));
        }

        return $entity;
    }

    /**
     * @return HttpKernelInterface
     */
    protected function getKernel()
    {
        return $this->get('kernel');
    }

    /**
     * @return \Doctrine\ORM\EntityManager
     */
    protected function getManager()
    {
        return $this->getDoctrine()->getManager();
    }

    /**
     * @param string $class
     *
     * @return \Doctrine\ORM\EntityRepository
     */
    protected function getRepository($class)
    {
        return $this->getManager()->getRepository($class);
    }

    /**
     * @param string $message
     *
     * @return BadRequestHttpException
     */
    protected function createBadRequestException($message = null)
    {
        return new BadRequestHttpException($message);
    }

    /**
     * Remove additional service parameters like `include_headers` before parameters validation.
     *
     * @param Request $request
     *
     * @return array
     */
    protected function removeAdditionalParameters(Request $request)
    {
        $params = $request->query->all();
        unset($params['include_headers']);
        unset($params['include']);
        unset($params['page']);
        unset($params['count']);

        return $params;
    }

    /**
     * @return DeskproContainer|\Symfony\Component\DependencyInjection\ContainerInterface
     */
    protected function getContainer()
    {
        return $this->container;
    }

    /**
     * @return \DeskPRO\Bundle\AppBundle\Cache\EtagGenerator
     */
    protected function getEtagGenerator()
    {
        return $this->get('etag_generator');
    }

    /**
     * @param $parameters
     *
     * @return string
     */
    protected function generateEtag($parameters)
    {
        return $this->getEtagGenerator()->generate($parameters);
    }

    /**
     * @return \DeskPRO\Bundle\AppBundle\Cache\VersionService
     */
    protected function getVersionService()
    {
        return $this->get('cache.version_service');
    }

    /**
     * @return string
     */
    protected function getThisVersionId()
    {
        return $this->getVersionService()->getVersion(TypeUtils::getBaseTypeName($this));
    }

    protected function regenerateThisVersionId()
    {
        $this->getVersionService()->newVersion(TypeUtils::getBaseTypeName($this));
    }

    /**
     * @return \DeskPRO\Bundle\AppBundle\Cache\Resolver\FileResolver
     */
    protected function getCacheResolver()
    {
        return $this->get('cache.resolver');
    }

    /**
     * @param Request $request
     *
     * @return null|\Symfony\Component\HttpFoundation\Response
     */
    protected function getCachedResponse(Request $request, $etag)
    {
        $response = $this->get('cache.resolver')->resolve($request);

        return $response && $response->getEtag() === $etag ? $this->getCacheResolver()->restoreResponseBody($response) : null;
    }

    /**
     * Logs an exception as system alert.
     *
     * This is used to track exceptions which we want to let users be aware of, depending on the exception type and
     * its' handler, once exceptional situation reaches its' critical point, we'll show a system incident in the
     * admin area.
     *
     * Don't use this to log handled exceptions which have no value for end users.
     *
     * @param \Exception $exception
     */
    protected function logException(\Exception $exception)
    {
        SystemErrorHandler::logException($exception);
    }

    /**
     * @param Request $request
     *
     * @return array
     */
    protected function getRequestData(Request $request)
    {
        $params = $request->request->all();
        $files  = $request->files->all();

        if (is_array($params) && is_array($files)) {
            $data = array_replace_recursive($params, $files);
        } else {
            $data = $params ?: $files;
        }

        return $data;
    }
}
