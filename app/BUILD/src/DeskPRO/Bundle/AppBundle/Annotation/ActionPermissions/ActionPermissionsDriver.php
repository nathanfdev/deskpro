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

namespace DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions;

use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiTags;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Metadata\MethodMetadata;
use DeskPRO\Bundle\AppBundle\Annotation\Exception\AbstractClassException;
use DeskPRO\Component\Util\StringUtils;
use Doctrine\Common\Annotations\Reader;
use Metadata\Driver\DriverInterface;
use Metadata\MergeableClassMetadata;

/**
 * Class ActionPermissionsDriver.
 */
class ActionPermissionsDriver implements DriverInterface
{
    /**
     * @var Reader
     */
    private $reader;

    /**
     * @param Reader $reader
     */
    public function __construct(Reader $reader)
    {
        $this->reader = $reader;
    }

    /**
     * @param \ReflectionClass $class
     *
     * @throws AbstractClassException - it should be thrown only when using cli cache:warmup
     *
     * @return MergeableClassMetadata
     */
    public function loadMetadataForClass(\ReflectionClass $class)
    {
        if ($class->isAbstract() || $class->isTrait() || $class->isInterface()) {
            throw new AbstractClassException(sprintf('Skip [ %s ] class. It\'s abstract, trait or interface', $class->getName()));
        };
        $classMetadata = new MergeableClassMetadata($class->getName());

        $classModes = $this->getClassModes($class);
        $classTags  = $this->getClassTags($class);

        foreach ($class->getMethods(\ReflectionMethod::IS_PUBLIC) as $method) {
            $methodMetadata = new MethodMetadata($class->getName(), $method->getName());
            $this->addMethodModes($methodMetadata, $method, $classModes);
            $this->addMethodTags($methodMetadata, $method, $classTags);
            $classMetadata->addMethodMetadata($methodMetadata);
        }

        return $classMetadata;
    }

    /**
     * @param \ReflectionClass $class
     *
     * @return null|object
     */
    protected function getClassModes(\ReflectionClass $class)
    {
        return $this->reader->getClassAnnotation($class, ApiModes::class);
    }

    /**
     * @param \ReflectionClass $class
     *
     * @return null|object
     */
    protected function getClassTags(\ReflectionClass $class)
    {
        return $this->reader->getClassAnnotation($class, ApiTags::class);
    }

    /**
     * @param MethodMetadata    $metadata
     * @param \ReflectionMethod $method
     * @param $classModes
     */
    protected function addMethodModes(MethodMetadata $metadata, \ReflectionMethod $method, $classModes)
    {
        $modes = $this->reader->getMethodAnnotation($method, ApiModes::class);

        if ($modes && $modes instanceof ApiModes) {
            $metadata->setModes($modes->getModes());
        } elseif ($classModes) {
            /* @var ApiModes $classModes*/
            $metadata->setModes($classModes->getModes());
        }
    }

    /**
     * @param MethodMetadata    $metadata
     * @param \ReflectionMethod $method
     * @param $classTags
     */
    protected function addMethodTags(MethodMetadata $metadata, \ReflectionMethod $method, $classTags)
    {
        $tags = $this->reader->getMethodAnnotation($method, ApiTags::class);

        $explicit_tags = [];
        if ($tags && $tags instanceof ApiTags) {
            $explicit_tags = $tags->getTags();
        } elseif ($classTags) {
            /* @var ApiTags $classTags */
            $explicit_tags = $classTags->getTags();
        }

        $tags = array_merge($explicit_tags, $this->getImplicitTags($metadata));
        $metadata->setTags($tags);
    }

    public function getImplicitTags(MethodMetadata $metadata)
    {
        $matches = [];
        if (preg_match_all('#(.*?\\Controller)#i', $metadata->class, $matches)) {
            $fqcn = ltrim($matches[0][1], '\\');
        } else {
            $fqcn = $metadata->class;
        }
        $tag   = explode('\\', $fqcn);
        $tag[] = $metadata->name;
        foreach ($tag as &$t) {
            $t = str_replace('/Controller/', '', $t);
            $t = StringUtils::toSnakeCase($t);
        }
        $tag = implode('.', $tag);

        return [$tag];
    }
}
