<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2016, DeskPRO Ltd.
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
namespace DeskPRO\Bundle\ApiBundle\ApiDoc\Extractor;

use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDoc as DpApiDoc;
use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDocSection;
use DeskPRO\Bundle\ApiBundle\Controller\CrudController;
use DeskPRO\Component\Util\TypeUtils;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Nelmio\ApiDocBundle\Extractor\ApiDocExtractor as BaseApiDocExtractor;
use Symfony\Component\Routing\Route;

/**
 * Class ApiDocExtractor.
 */
class ApiDocExtractor extends BaseApiDocExtractor
{
    /**
     * @var array
     */
    protected $action_list = [
        'list'   => true,
        'get'    => true,
        'post'   => true,
        'put'    => true,
        'delete' => false,
    ];

    /**
     * @return Route[]
     */
    public function getRoutes()
    {
        return array_filter($this->router->getRouteCollection()->all(), function (Route $r) {
            $ctrl = $r->getDefault('_controller');

            $exposed = true;
            if ($reflection = $this->extractControllerReflection($r)) {
                $action = TypeUtils::cleanAction($ctrl, true);
                if (
                    $reflection->isSubclassOf(CrudController::class)
                    && in_array($action, array_keys($this->action_list))
                    && $expose = $reflection->getProperty('exposeOnly')->getValue()
                ) {
                    $exposed = in_array($action, $expose);
                }
            }

            return $ctrl && $reflection && $exposed;
        });
    }

    /**
     * This method extends basic Nelmio`s and provide the ability to interact with DP CrudController.
     *
     * @param ApiDoc            $annotation
     * @param Route             $route
     * @param \ReflectionMethod $method
     *
     * @return ApiDoc
     */
    protected function extractData(ApiDoc $annotation, Route $route, \ReflectionMethod $method)
    {
        if ($annotation instanceof DpApiDoc
            && ($class_reflection = $this->getClassReflection($method, $route))
        ) {
            if (!$annotation->getOutput()
                && in_array(TypeUtils::cleanAction($method->name, true), $this->getCreativeMethods())
                && $output = $class_reflection->getStaticPropertyValue('output_entity', null)
            ) {
                if (TypeUtils::cleanAction($method->name, true) === 'list') {
                    $output = "array<$output>";
                }
                $annotation->setClassOutput($output);
            }

            if (!$annotation->getSection()
                && ($section_annotation = $this->reader->getClassAnnotation($class_reflection, ApiDocSection::class))
                && ($section_annotation instanceof ApiDocSection)
            ) {
                $annotation->setSection($section_annotation->getSection());
                unset($section_annotation);
            }
        }

        $extracted_annotation = parent::extractData($annotation, $route, $method);
        if ($annotation instanceof DpApiDoc) {
            $annotation->setSection('');
            $annotation->setClassOutput('');
        }

        return $extracted_annotation;
    }

    /**
     * Get reflection class for controller.
     *
     * @param \ReflectionMethod $method
     * @param Route             $route
     *
     * @return bool|\ReflectionClass|void
     */
    protected function getClassReflection(\ReflectionMethod $method, Route $route)
    {
        $class_reflection = false;
        if (strpos($method->class, 'CrudController') !== false || strpos($method->class, 'CrudSubController')) {
            $class_reflection = $this->extractControllerReflection($route);
        }
        if (!$class_reflection) {
            $class_reflection = new \ReflectionClass($method->class);
        }

        if ($class_reflection->isSubclassOf(CrudController::class)) {
            return $class_reflection;
        }

        return false;
    }

    /**
     * return the list of methods that should return some output.
     *
     * @return array
     */
    protected function getCreativeMethods()
    {
        $return = [];
        foreach ($this->action_list as $action => $creative) {
            if ($creative) {
                $return[] = $action;
            }
        }

        return $return;
    }

    /**
     * Extract \ReflectionClass from route default _controller attribute.
     *
     * @param Route $route
     *
     * @return \ReflectionClass|void
     */
    protected function extractControllerReflection(Route $route)
    {
        $ctrl          = $route->getDefault('_controller');
        $parts         = explode('::', $ctrl);
        $is_controller = preg_match('#^DeskPRO\\\\Bundle\\\\ApiBundle\\\\#', $ctrl);

        if ($is_controller && $parts[0]) {
            return new \ReflectionClass($parts[0]);
        }

        return;
    }
}
