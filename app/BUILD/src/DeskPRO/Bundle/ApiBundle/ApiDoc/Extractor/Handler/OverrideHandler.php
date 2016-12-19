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

namespace DeskPRO\Bundle\ApiBundle\ApiDoc\Extractor\Handler;

use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDoc as DpApiDoc;
use DeskPRO\Bundle\ApiBundle\ApiDoc\Extractor\ApiDocExtractor;
use DeskPRO\Component\Util\ControllerUtils;
use Doctrine\Common\Annotations\Reader;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Nelmio\ApiDocBundle\Extractor\HandlerInterface;
use Symfony\Component\Routing\Route;

/**
 * Class OverrideHandler.
 */
class OverrideHandler implements HandlerInterface
{
    /**
     * @var Reader
     */
    private $reader;

    /**
     * @var array
     */
    private $annotationsList = [];

    /**
     * OverrideHandler constructor.
     *
     * @param Reader $reader
     */
    public function __construct(Reader $reader)
    {
        $this->reader = $reader;
    }

    /**
     * @param ApiDoc            $annotation
     * @param array             $annotations
     * @param Route             $route
     * @param \ReflectionMethod $method
     */
    public function handle(ApiDoc $annotation, array $annotations, Route $route, \ReflectionMethod $method)
    {
        if ($annotation instanceof DpApiDoc
            && ($classReflection = ControllerUtils::extractControllerReflection($route))
        ) {
            $this->getClassAnnotations($classReflection);

            $action = ControllerUtils::cleanAction($method->getName());

            $this->overrideWith('all', $annotation, $action);
            $this->overrideWith($method->getName(), $annotation, $action);
        }
    }

    /**
     * return the list of methods that should return some output.
     *
     * @return array
     */
    private function getMethodsWithOutput()
    {
        $return = [];
        foreach (ApiDocExtractor::$methods as $action => $creative) {
            if ($creative) {
                $return[] = $action;
            }
        }

        return $return;
    }

    /**
     * @param \ReflectionClass $classReflection
     */
    private function getClassAnnotations(\ReflectionClass $classReflection)
    {
        $this->annotationsList = [];
        foreach ($this->reader->getClassAnnotations($classReflection) as $annotation) {
            if ($annotation instanceof DpApiDoc) {
                $targets = explode(',', $annotation->getTarget());
                foreach ($targets as $target) {
                    $target = trim($target);
                    if (!isset($this->annotationsList[$target])) {
                        $this->annotationsList[$target] = [];
                    }
                    $this->annotationsList[$target][] = $annotation;
                }
            }
        }
    }

    /**
     * @param string   $key
     * @param DpApiDoc $annotation
     * @param string   $action
     */
    private function overrideWith($key, DpApiDoc $annotation, $action)
    {
        if (isset($this->annotationsList[$key])) {
            $overrides = $this->annotationsList[$key];

            $this->override($annotation, $overrides, $action, $key !== 'all');
        }
    }

    /**
     * @param DpApiDoc   $annotation
     * @param DpApiDoc[] $overrides
     * @param string     $action
     * @param bool       $extended
     */
    private function override(DpApiDoc $annotation, array $overrides, $action, $extended)
    {
        foreach ($overrides as $override) {
            $this->overrideOutput($annotation, $override, $action);
            $this->overrideSection($annotation, $override);
            $this->overrideInput($annotation, $override, $action);
            $this->overrideTags($annotation, $override);
            if ($extended) {
                $this->overrideFilters($annotation, $override);
                $this->overrideRequirements($annotation, $override);
                $this->overrideParameters($annotation, $override);
                $this->overrideDescription($annotation, $override);
            }
        }
    }

    /**
     * @param DpApiDoc $annotation
     * @param DpApiDoc $override
     * @param string   $action
     */
    private function overrideOutput(DpApiDoc $annotation, DpApiDoc $override, $action)
    {
        if (!$annotation->getOutput()
            && in_array($action, $this->getMethodsWithOutput())
            && $override->getOutput()
        ) {
            $output = $override->getOutput();
            if ($action === 'list') {
                $output = "array<{$output}>";
            }
            $annotation->setClassOutput($output);
        }
    }

    /**
     * @param DpApiDoc $annotation
     * @param DpApiDoc $override
     */
    private function overrideSection(DpApiDoc $annotation, DpApiDoc $override)
    {
        if (!$annotation->getSection()
            && $override->getSection()
        ) {
            $annotation->setSection($override->getSection());
        }
    }

    /**
     * @param DpApiDoc $annotation
     * @param DpApiDoc $override
     * @param string   $action
     */
    private function overrideInput(DpApiDoc $annotation, DpApiDoc $override, $action)
    {
        if (($action === 'put' || $action === 'post') && !$annotation->getInput() && $override->getInput()) {
            $annotation->setClassInput($override->getInput());
        }
    }

    /**
     * @param DpApiDoc $annotation
     * @param DpApiDoc $override
     */
    private function overrideFilters(DpApiDoc $annotation, DpApiDoc $override)
    {
        foreach ($override->getFilters() as $name => $filter) {
            $annotation->addFilter($name, $filter);
        }
    }

    /**
     * @param DpApiDoc $annotation
     * @param DpApiDoc $override
     */
    private function overrideRequirements(DpApiDoc $annotation, DpApiDoc $override)
    {
        $annotation->setRequirements($override->getRequirements());
    }

    /**
     * @param DpApiDoc $annotation
     * @param DpApiDoc $override
     */
    private function overrideParameters(DpApiDoc $annotation, DpApiDoc $override)
    {
        $annotation->setParameters(array_merge($annotation->getParameters(), $override->getParameters()));
    }

    /**
     * @param DpApiDoc $annotation
     * @param DpApiDoc $override
     */
    private function overrideDescription(DpApiDoc $annotation, DpApiDoc $override)
    {
        if ($override->getDescription()) {
            $annotation->setDescription($override->getDescription());
        }
    }

    private function overrideTags(DpApiDoc $annotation, DpApiDoc $override)
    {
        $arrayData = $override->toArray();
        if (isset($arrayData['tags'])) {
            foreach ($arrayData['tags'] as $tag => $colorCode) {
                $annotation->addTag($tag, $colorCode);
            }
        }
    }
}
