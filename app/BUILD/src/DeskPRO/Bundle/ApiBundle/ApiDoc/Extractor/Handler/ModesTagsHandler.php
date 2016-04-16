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
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Metadata\MethodMetadata;
use DeskPRO\Bundle\AppBundle\Annotation\Exception\AbstractClassException;
use DeskPRO\Bundle\AppBundle\Annotation\Metadata\MetadataFactory;
use DeskPRO\Component\Util\ControllerUtils;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Nelmio\ApiDocBundle\Extractor\HandlerInterface;
use Symfony\Component\Routing\Route;

class ModesTagsHandler implements HandlerInterface
{
    protected $factory;

    public function __construct(MetadataFactory $factory)
    {
        $this->factory = $factory;
    }

    public function handle(ApiDoc $annotation, array $annotations, Route $route, \ReflectionMethod $method)
    {
        try {
            $class         = ControllerUtils::extractControllerClass($route);
            $classMetadata = $this->factory->getMetadataForClass($class);
            if ($classMetadata->methodMetadata[$method->name]) {
                /** @var MethodMetadata $methodMetadata */
                $methodMetadata = $classMetadata->methodMetadata[$method->name];

                if ($annotation instanceof DpApiDoc) {
                    $annotation->setApiModes($methodMetadata->getModes());
                    $annotation->setApiTags($methodMetadata->getTags());
                }
            }
        } catch (AbstractClassException $e) {
            $a = 1;
            // keep the silence
        }
    }
}
