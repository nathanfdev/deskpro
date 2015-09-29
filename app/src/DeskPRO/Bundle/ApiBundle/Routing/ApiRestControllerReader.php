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

namespace DeskPRO\Bundle\ApiBundle\Routing;

use Doctrine\Common\Annotations\Reader;
use FOS\RestBundle\Routing\Loader\Reader\RestActionReader;
use FOS\RestBundle\Routing\Loader\Reader\RestControllerReader;
use FOS\RestBundle\Routing\RestRouteCollection;
use Symfony\Component\Config\Resource\FileResource;

/**
 * REST controller reader.
 */
class ApiRestControllerReader extends RestControllerReader
{
    private $actionReader;
    private $annotationReader;

    /**
     * Initializes controller reader.
     *
     * @param RestActionReader $actionReader     action reader
     * @param Reader           $annotationReader annotation reader
     */
    public function __construct(RestActionReader $actionReader, Reader $annotationReader)
    {
        $this->actionReader     = $actionReader;
        $this->annotationReader = $annotationReader;
    }

    /**
     * Returns action reader.
     *
     * @return RestActionReader
     */
    public function getActionReader()
    {
        return $this->actionReader;
    }

    /**
     * Reads controller routes.
     *
     * @param \ReflectionClass $reflectionClass
     *
     * @throws \InvalidArgumentException
     *
     * @return RestRouteCollection
     */
    public function read(\ReflectionClass $reflectionClass)
    {
        $collection = new RestRouteCollection();
        $collection->addResource(new FileResource($reflectionClass->getFileName()));

        // read prefix annotation
        if ($annotation = $this->readClassAnnotation($reflectionClass, 'Prefix')) {
            $this->actionReader->setRoutePrefix($annotation->value);
        }

        // read name-prefix annotation
        if ($annotation = $this->readClassAnnotation($reflectionClass, 'NamePrefix')) {
            $this->actionReader->setNamePrefix($annotation->value);
        }

        $resource = array();
        // read route-resource annotation
        if ($annotation = $this->readClassAnnotation($reflectionClass, 'RouteResource')) {
            $resource = array($annotation->resource);
        } elseif ($reflectionClass->implementsInterface('FOS\RestBundle\Routing\ClassResourceInterface')) {
            $resource = preg_split(
                '/([A-Z][^A-Z]*)Controller/', $reflectionClass->getShortName(), -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE
            );
            if (empty($resource)) {
                throw new \InvalidArgumentException("Controller '{$reflectionClass->name}' does not identify a resource");
            }
        }

        // trim '/' at the start of the prefix
        if ('/' === substr($prefix = $this->actionReader->getRoutePrefix(), 0, 1)) {
            $this->actionReader->setRoutePrefix(substr($prefix, 1));
        }

        // read action routes into collection
        foreach ($reflectionClass->getMethods(\ReflectionMethod::IS_PUBLIC) as $method) {
            $this->actionReader->read($collection, $method, $resource);
        }

        $this->actionReader->setRoutePrefix(null);
        $this->actionReader->setNamePrefix(null);
        $this->actionReader->setParents(array());

        return $collection;
    }

    /**
     * Reads class annotations.
     *
     * @param \ReflectionClass $reflectionClass
     * @param string           $annotationName
     *
     * @return object|null
     */
    private function readClassAnnotation(\ReflectionClass $reflectionClass, $annotationName)
    {
        $annotationClass = "FOS\\RestBundle\\Controller\\Annotations\\$annotationName";

        if ($annotation = $this->annotationReader->getClassAnnotation($reflectionClass, $annotationClass)) {
            return $annotation;
        }
    }
}
