<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2015, DeskPRO Ltd.
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
namespace DeskPRO\Bundle\ApiBundle\Controller;

use Application\DeskPRO\DependencyInjection\DeskproContainer;
use DeskPRO\Bundle\ApiBundle\View\Representation\StandardRepresentation;
use FOS\RestBundle\Controller\FOSRestController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

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
     * @param array|object $data
     * @param string       $includes_string
     * @param int          $type
     *
     * @return array
     */
    protected function dataSerialize($data, $includes_string = null, $type = self::DATA_TYPE_STANDARD)
    {
        // not passing an $includes_string will default to the master request's "include" GET param
        if (null === $includes_string) {
            $includes_string = $this->get('request_stack')->getMasterRequest()->query->get('include');
        }

        if (self::DATA_TYPE_COUNT_ONLY === $type) {
            if (is_object($data) && method_exists($data, 'count')) {
                return [
                    'meta' => [
                        'count'       => $data->count(),
                        'total_count' => $data->count(),
                    ],
                ];
            }

            return [
                'meta' => [
                    'count'       => $data['count'],
                    'total_count' => $data['count'],
                ],
            ];
        }

        return $this->get('data_serializer')->serialize($data, $includes_string);
    }

    /**
     * @param mixed $input any array or object
     *
     * @return StandardRepresentation
     */
    protected function createRepresentation($input)
    {
        return $this->get('api_view_representation_factory')->createRepresentation($input);
    }

    /**
     * @param $status
     * @param $code
     * @param $message
     * @param array|FormInterface $errors_data
     *
     * @return array
     */
    protected function createErrorRepresentation($status, $code, $message, $errors_data = [])
    {
        return $this->get('api_view_representation_factory')->createErrorRepresentation(
            $status,
            $code,
            $message,
            $errors_data
        );
    }

    /**
     * @param string $class
     * @param int    $id
     * @param string $message
     *
     * @return object
     */
    protected function findOr404($class, $id, $message = 'Not found')
    {
        if (!$entity = $this->getDoctrine()->getRepository($class)->find($id)) {
            throw $this->createNotFoundException($message);
        }

        return $entity;
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
}
