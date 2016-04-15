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
use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDocSection;
use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\OutputEntity;
use DeskPRO\Bundle\ApiBundle\Controller\CrudController;
use DeskPRO\Component\Util\ControllerUtils;
use Doctrine\Common\Annotations\Reader;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Nelmio\ApiDocBundle\Extractor\HandlerInterface;
use Symfony\Component\Routing\Route;

class OverrideHandler implements HandlerInterface
{
    private $reader;

    public function __construct(Reader $reader)
    {
        $this->reader = $reader;
    }

    public function handle(ApiDoc $annotation, array $annotations, Route $route, \ReflectionMethod $method)
    {
        if ($annotation instanceof DpApiDoc
            && ($class_reflection = ControllerUtils::extractControllerReflection($route))
        ) {
            if (!$annotation->getOutput()
                && in_array(ControllerUtils::cleanAction($method->name, true), $this->getCreativeMethods())
                && ($output = $this->reader->getClassAnnotation($class_reflection, OutputEntity::class))
                && ($output instanceof OutputEntity)
            ) {
                $output = $output->getOutput();
                if (ControllerUtils::cleanAction($method->name, true) === 'list') {
                    $output = "array<{$output}>";
                }
                $annotation->setClassOutput($output);
            }

            if (!$annotation->getSection()
                && ($section_annotation = $this->reader->getClassAnnotation($class_reflection, ApiDocSection::class))
                && ($section_annotation instanceof ApiDocSection)
            ) {
                $annotation->setSection($section_annotation->getSection());
            }
        }
    }

    /**
     * return the list of methods that should return some output.
     *
     * @return array
     */
    private function getCreativeMethods()
    {
        $return = [];
        foreach (CrudController::$methods as $action => $creative) {
            if ($creative) {
                $return[] = $action;
            }
        }

        return $return;
    }
}
