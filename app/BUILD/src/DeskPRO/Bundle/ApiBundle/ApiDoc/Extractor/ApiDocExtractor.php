<?php

namespace DeskPRO\Bundle\ApiBundle\ApiDoc\Extractor;

use DeskPRO\Bundle\ApiBundle\Controller\CrudController;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\Feature;
use DeskPRO\Component\Util\ControllerUtils;
use FOS\RestBundle\Controller\Annotations\Put;
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

    protected function parseAnnotations(ApiDoc $annotation, Route $route, \ReflectionMethod $method)
    {
        parent::parseAnnotations($annotation, $route, $method);

        $annots = $this->reader->getMethodAnnotations($method);
        foreach ($annots as $annot) {
            if ($annot instanceof Put) {
                $this->injectInputMethodOption($annotation);
            }
        }
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

    /**
     * @param ApiDoc $annotation
     */
    private function injectInputMethodOption(ApiDoc $annotation)
    {
        $input = $annotation->getInput();
        if (is_array($input) && array_key_exists('class', $input)) {
            $input['options']['method'] = 'put';
        } elseif (is_string($input)) {
            $input = ['class' => $input, 'options' => ['method' => 'put']];
        }

        $reflection    = new \ReflectionClass(ApiDoc::class);
        $inputProperty = $reflection->getProperty('input');
        $inputProperty->setAccessible(true);
        $inputProperty->setValue($annotation, $input);
    }
}
