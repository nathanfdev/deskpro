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

namespace DeskPRO\Bundle\ApiBundle\ApiDoc\Extractor;

use DeskPRO\Bundle\ApiBundle\Controller\CrudController;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\Feature;
use DeskPRO\Component\Util\ControllerUtils;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Nelmio\ApiDocBundle\Extractor\ApiDocExtractor as BaseApiDocExtractor;
use Symfony\Component\Routing\Route;

/**
 * Class ApiDocExtractor.
 */
class ApiDocExtractor extends BaseApiDocExtractor
{
    public static $methods = [
        'list'   => true,
        'count'  => true,
        'get'    => true,
        'post'   => true,
        'put'    => false,
        'delete' => false,
    ];

    /**
     * {@inheritdoc}
     */
    public function getRoutes()
    {
        $features          = $this->container->get('deskpro.feature_flags');
        $annotationsReader = $this->container->get('annotation_reader');

        return array_filter($this->router->getRouteCollection()->all(), function (Route $r) use ($annotationsReader, $features) {
            $ctrl = $r->getDefault('_controller');
            $action = $ctrl ? ControllerUtils::cleanAction($ctrl, true) : false;
            $reflection = ControllerUtils::extractControllerReflection($r);

            if (!$action || !$reflection) {
                return false;
            }

            // check feature annotation
            $method = explode('::', $ctrl);
            if (isset($method[1])) {
                $method = $reflection->getMethod($method[1]);

                $classAnnotation = $annotationsReader->getClassAnnotation($reflection, Feature::class);
                $methodAnnotation = $annotationsReader->getMethodAnnotation($method, Feature::class);

                /** @var Feature $annotation */
                $annotation = $methodAnnotation ?: $classAnnotation;
                $name = $annotation ? $annotation->getName() : null;
                if ($annotation && !($features->hasFeature($name) || $features->hasBeta($name))) {
                    return false;
                }
            }

            // check exposed
            if (!$this->isExposedAction($action, $reflection)) {
                return false;
            }

            return true;
        });
    }

    /**
     * {@inheritdoc}
     */
    public function all($view = ApiDoc::DEFAULT_VIEW)
    {
        $extracted = parent::all($view);

        // validate annotations
        // we need to make sure all actions have description, input/output info etc
        $failures = [];

        foreach ($extracted as $action) {
            /** @var \DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDoc $annotation */
            $annotation = $action['annotation'];
            $path       = $annotation->getRoute()->getPath();
            $method     = $annotation->getMethod();

            $missing = [];
            if (!$annotation->getSection()) {
                $missing[] = 'section';
            }
            if (!$annotation->getDescription()) {
                $missing[] = 'description';
            }
            if (in_array($method, ['GET', 'POST'])
                && !$annotation->getOutput()
                && !$annotation->isNoOutput()) {
                $missing[] = 'output';
            }
            if (in_array($method, ['POST', 'PUT'])
                && !$annotation->getInput()
                && !$annotation->getParameters()
                && !$annotation->isNoInput()) {
                $missing[] = 'input';
            }

            if (count($missing)) {
                $failures[] = 'No api doc '.implode(', ', $missing)." for $method $path";
            }
        }

        if (count($failures)) {
            throw new \Exception(implode('. ', $failures));
        }

        return $extracted;
    }

    /**
     * @param string           $action
     * @param \ReflectionClass $reflection
     *
     * @return bool
     */
    protected function isExposedAction($action, \ReflectionClass $reflection)
    {
        if ($reflection->isSubclassOf(CrudController::class)
           && ($exposedMethods = $this->getExposedActions($action, $reflection)) !== false) {
            // this is crud, and exposOnly is set, so we gonna check it
            return in_array($action, $exposedMethods);
        }

        // by default everything is exposed
        return true;
    }

    /**
     * @param string           $action
     * @param \ReflectionClass $reflection
     *
     * @return bool|array
     */
    protected function getExposedActions($action, \ReflectionClass $reflection)
    {
        if (!in_array($action, array_keys(self::$methods))) {
            // This method is custom for crud - e.g. getMySuperListAction, shouldn't process it
            return false;
        }

        $exposedMethods = $reflection->getProperty('exposeOnly');
        $exposedMethods = $exposedMethods ? $exposedMethods->getValue() : null;

        if (!is_array($exposedMethods)) {
            // exposeOnly was not set, so everything is exposed
            return false;
        }

        return $exposedMethods;
    }
}
