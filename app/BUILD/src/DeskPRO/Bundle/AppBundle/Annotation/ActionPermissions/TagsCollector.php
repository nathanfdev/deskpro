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

use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Metadata\ActionPermissionsMetadataFactory;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Metadata\MethodMetadata;
use DeskPRO\Bundle\AppBundle\Annotation\Exception\AbstractClassException;
use DeskPRO\Bundle\AppBundle\Security\Authorization\ActionPermissionsHelper;
use Gnugat\NomoSpaco\File\FileRepository;
use Gnugat\NomoSpaco\FqcnRepository;
use Gnugat\NomoSpaco\Token\ParserFactory;

/**
 * Class TagsCollector.
 */
class TagsCollector
{
    /**
     * @var array
     */
    protected $tags = [];

    /**
     * @var ActionPermissionsHelper
     */
    protected $helper;

    /** @var ActionPermissionsMetadataFactory */
    protected $factory;

    /**
     * @param ActionPermissionsHelper          $helper
     * @param ActionPermissionsMetadataFactory $factory
     */
    public function __construct(ActionPermissionsHelper $helper, ActionPermissionsMetadataFactory $factory)
    {
        $this->helper  = $helper;
        $this->factory = $factory;
    }

    /**
     * @return array
     */
    public function getClasses()
    {
        $fqcn_repo = new FqcnRepository(new FileRepository(), new ParserFactory());

        return array_merge(
            @$fqcn_repo->findIn(DP_ROOT.'/src/DeskPRO/Bundle/ApiBundle/Controller'),
            @$fqcn_repo->findIn(DP_ROOT.'/src/Application/LegacyApiBundle/Controller')
        );
    }

    /**
     * @param $tags
     */
    public function addTags($tags)
    {
        $this->tags = array_merge($this->tags, $tags);
    }

    public function unique()
    {
        $this->tags = array_unique($this->tags);
    }

    /**
     * @return array
     */
    public function getTagsHierarchy()
    {
        $this->unique();
        $tags_hierarhy = call_user_func_array('array_merge_recursive', array_map([$this->helper, 'createActionTagsHierarchy'], $this->tags));

        return $tags_hierarhy;
    }

    public function collectTags($force_reload = false)
    {
        foreach ($this->getClasses() as $class) {
            try {
                $classMetadata = $this->factory->getMetadataForClass($class, $force_reload);
                $metadata[]    = $classMetadata;
                /** @var \Metadata\ClassMetadata $metadatum */
                foreach ($classMetadata->methodMetadata as $metadatum) {
                    /* @var MethodMetadata $metadatum */
                    $this->addTags($metadatum->getTags());
                }
            } catch (AbstractClassException $e) {
                // There is nothing to do. Or just output it
            } catch (\ReflectionException $e) {
                // TODO: we should dive into FQCN to know why it return directories as FQCN.
            }
        }
    }
}
